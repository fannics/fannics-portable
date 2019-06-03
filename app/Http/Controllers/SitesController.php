<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSiteStep1Request;
use App\Http\Requests\CreateSiteStep2Request;
use App\Jobs\CdnSite;
use App\Server;
use App\SiteServices;
use Forge;
use Illuminate\Http\Request;
use Themsaid\Forge\Exceptions\ValidationException;
use App\Site;
use URL;
use App\Version;
use App\Helpers\DeploymentHelper;
use Carbon\Carbon;
use Log;

class SitesController extends Controller
{

    public function index()
    {
        $sites = Site::with('server')->whereNotIn('domain',array_values(config('fannics-portal.domains')))
                                     ->get();

        return view('sites.index',compact('sites'));
    }

    public function createStep1()
    {
        if (empty(Version::count()))
        {
            return redirect()->back()->withErrors('create version first before creating a site');
        }
        $servers = Server::where('steps',Server::OPERATIONAL)->get();

        return view('sites.createStep1',compact('servers'));
    }

    public function storeStep1(CreateSiteStep1Request $request)
    {
        $serverId = $request->input('server');
        $allSites = Forge::sites($serverId);

        foreach ($allSites as $site)
        {
            if ($site->name == $request->input('domain'))
            {
                return redirect()->back()->withErrors(['check your site domain is valid or maybe the domain is taken']);
            }

            if ($site->name == $request->input('cdn_domain'))
            {
                return redirect()->back()->withErrors(['check your cdn address is valid or maybe the cdn address is taken']);
            }
        }

        try {
            Log::info('store site step1 in SitesController');
            $site = Forge::createSite($serverId, [
                'domain' => $request->input('domain'),
                'project_type' => 'php',
                'directory' => '/public'
            ],$wait = false);

           $siteDb =  Site::create([
                'forge_site_id' => $site->id,
                'server_id' => $serverId,
                'name' => $request->input('name'),
                'domain' => $request->input('domain'),
                'protocol' => $request->input('protocol'),
                'cdn_domain' => $request->input('cdn_domain'),
                'cdn_protocol' => $request->input('cdn_protocol'),
                'version_id' => Version::latest()->id
            ]);

            $job = (new CdnSite($serverId,$siteDb->id,$request->input('cdn_domain')))
                ->delay(Carbon::now()->addMinutes(4));

            dispatch($job);

        }
        catch (ValidationException $e)
        {
            return redirect()->back()->withErrors(['check your site name is valid or maybe site name is taken']);
        }

        $redirectRoute = redirect()->route('sites.create.step2',$siteDb->id);

       return !is_null($request->input('protocol')) ? $redirectRoute->with(['success' => true , 'message' => 'you must install an SSL Certificate to have the site working']) : $redirectRoute;

    }

    public function createStep2($siteId)
    {
        $siteDb = Site::find($siteId);

        if (is_null($siteDb))
        {
            return redirect()->back()->withErrors(['site not found']);
        }

        if ($siteDb->steps >= Site::CREATED)
        {
            return redirect()->back()->withErrors(['site already made step 2 and created']);
        }
        Log::info('getting forge site from step 2 create inside SitesController');
        $forgeSite = Forge::site($siteDb->server_id,$siteDb->forge_site_id);

        $services = Version::allServices();

       return view('sites.createStep2',compact('siteDb','forgeSite','services'));
    }

    public function checkSiteInstalled($siteId)
    {
        $siteDb = Site::find($siteId);

        Log::info('check forge site is installed inside SitesController');

        $site = Forge::site($siteDb->server_id,$siteDb->forge_site_id);

        if ($site->repositoryBranch == null )
        {
            Log::info('install git repository on forge site inside SitesController');

            Forge::installGitRepositoryOnSite($site->serverId, $site->id, [
                'provider' => 'bitbucket',
                'repository' => 'ivanfc/fannics',
                'branch' => 'develop-new'
            ]);
        }

        if ($site->repositoryStatus != 'installed')
        {
            return response()->json(['status' => 'fail' , 'messages' => ['Create Site Step 2 - Getting ready, please wait']]);
        }

        return response()->json(['status' => 'success' , 'messages' => ['Continue creating the site - Step 2']]);
    }

    public function checkIdsStatus(Request $request)
    {
        $response = [];
        try{

        foreach ($request->input('ids') as $site)
        {
           $dbSite = Site::where('forge_site_id',$site['id'])->first();

            if (is_null($dbSite))
            {
                $response [] = ['id' => $site['id'], 'status' => 'deleted'];
                continue;

            }

           $status = $dbSite->getCurrentStatus();

           $response [] = ['id' => $site['id'], 'status' => $status];
        }
        }
        catch(\Exception $e)
        {
            Log::error('we catch 15 seconds exception');
        }
        return response()->json($response);
    }

    public function storeStep2(CreateSiteStep2Request $request,$siteId)
    {
        $siteDb = Site::find($siteId);
        $serverId = $siteDb->server_id;

        $envContent = $this->formEnvFile($request,$siteDb);

        try {
            Log::info('creating mysql database inside SitesController');

            $database =   Forge::createMysqlDatabase($serverId,[
                'name' => $request->input('db_name'),
            ], $wait = false);
        }

        catch (ValidationException $e)
        {
            return redirect()->back()->withErrors(['database or username are already taken try to change them']);
        }

        try {
            Log::info('creating mysqlUser database inside SitesController');

            Forge::createMysqlUser($serverId,[
                'name' => $request->input('db_user'),
                'password' => $request->input('db_pw'),
                'databases' => [$database->id]
            ],$wait = false);
        }
        catch (ValidationException $e)
        {
            Forge::deleteMysqlDatabase($serverId,$database->id);
            return redirect()->back()->withErrors(['try to check user name or change it']);
        }
        Log::info('updating site environment inside SitesController');

        Forge::updateSiteEnvironmentFile($serverId,$siteDb->forge_site_id
            , $envContent, $wait =false);

        sleep(2);

        Log::info('getting site inside store step2 inside SitesController');
        $site = Forge::site($serverId,$siteDb->forge_site_id);

        $adminEmail = $request->input('admin_email');
        $adminPw = $request->input('admin_pw');
        $adminCommand = "php artisan create:admin " .$adminEmail . " " . $adminPw;

        Log::info('updating deployment script inside SitesController');
        $site->updateDeploymentScript(DeploymentHelper::getScript($siteDb). $adminCommand,$wait=true);
        Log::info('making deploy inside SitesController');
        $site->deploySite();

        $siteDb->update(['steps' => Site::CREATED ,'step_two_filled' => 1]);

        SiteServices::create([
            'site_id' => $siteDb->id,
            'services' => json_encode($request->input('services'))
        ]);

        return redirect()->route('sites')->with(['success' => true , 'message' => 'Site Created Please Wait 5 - 10 Minutes Then Check The URL']);
    }

    private function formEnvFile ($request,$siteDb)
    {
        $dbUser = $request->input('db_user');
        $dbPw = $request->input('db_pw');
        $dbName = $request->input('db_name');
        $rollbarToken = $request->input('services')['rollbar']['token'];

        return "SITE_NAME=$siteDb->name\nLOCALE=en\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_KEY=".str_random(32)."\nAPP_URL=".$siteDb->protocol."://".$siteDb->domain."/\nDB_HOST=localhost\nDB_DATABASE=".$dbName."\nDB_USERNAME=".$dbUser ."\nDB_PASSWORD=".$dbPw."\nCACHE_DRIVER=file\nSESSION_DRIVER=file\nROLLBAR_TOKEN=".$rollbarToken."\nROLLBAR_LEVEL=error\nTHUMBOR=".$siteDb->cdn_protocol."://".$siteDb->cdn_domain;
    }

}

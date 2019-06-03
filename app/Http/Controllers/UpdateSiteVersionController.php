<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteVersionRequest;
use App\Site;
use App\SiteServices;
use App\Version;
use Illuminate\Http\Request;
use Forge;
use App\Helpers\DeploymentHelper;
use Log;

class UpdateSiteVersionController extends Controller
{
    public function edit($siteId)
    {
        $site = Site::find($siteId);

        $versions = Version::after($site->version->number)->sortBy('number');

        $firstServices = $versions->isEmpty() ? [] : array_diff($versions->first()->services(),$site->definedServices());

        $firstVersionNote = '';

        if(!$versions->isEmpty() && !is_null(config('fannics-versions-notes.' . $versions->first()->name)) )
        {
            $firstVersionNote = config('fannics-versions-notes.' . $versions->first()->name);
        }

        return view('update-site-version.edit',compact('site','versions','firstServices','firstVersionNote'));
    }

    public function update(UpdateSiteVersionRequest $request, $siteId)
    {
        $siteDb = Site::find($siteId);

        $siteDb->update(['version_id' => $request->input('version') , 'steps' => Site::UPDATING]);

        Log::info('getting site inside UpdateSiteVersionController');
        $forgeSite = Forge::site($siteDb->server_id,$siteDb->forge_site_id);
        Log::info('updating deployment script inside UpdateSiteVersionController');
        $forgeSite->updateDeploymentScript(DeploymentHelper::getScript($siteDb));
        Log::info('deploying site inside UpdateSiteVersionController');
        $forgeSite->deploySite();

        $siteServices = json_decode(SiteServices::where('site_id',$siteDb->id)->first()->services,true);

        $siteServices = (!is_null($request->input('services')) ? array_merge($request->input('services'),$siteServices) : $siteServices);

        SiteServices::where('site_id',$siteDb->id)->update([
            'services' => json_encode($siteServices)
        ]);

        return redirect()->route('sites')->with(['success' => true , 'message' => 'Site is updating, once done. The site status will change from Updating to Operational.']);
    }

    public function getServices(Request $request)
    {
        $site = Site::find($request->input('siteId'));

        $version = Version::find($request->input('versionId'));

       $services =  array_diff($version->services(),$site->definedServices());

        $servicesDetails = [];
        foreach($services as $code){
            $serviceDetail = config('fannics-services.'.$code) ? : [];
            foreach($serviceDetail as $name => $label){
                $servicesDetails[] = ['code' => $code, 'name' => $name , 'label' => $label];

            }
        }

        return response()->json(['services' => $servicesDetails ,'notes' => $version->configNotes()]);
    }

}

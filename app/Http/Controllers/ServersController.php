<?php

namespace App\Http\Controllers;

use App\Jobs\RunRecipe;
use Themsaid\Forge\Exceptions\ValidationException;
use App\Services\CustomForge;
use App\Site;
use Illuminate\Http\Request;
use App\Server;
use App\Http\Requests\CreateServerStep1Request;
use Forge;
use Carbon\Carbon;
use SSH;
use Log;

class ServersController extends Controller
{
    public function index()
    {
        $servers = Server::all();

        return view('servers.index',compact('servers'));
    }

    public function createStep1()
    {
        return view('servers.createStep1');
    }

    public function storeStep1(CreateServerStep1Request $request)
    {
        $customForge = new CustomForge();

        $ip = $request->input('ip');
        $sshUser= $request->input('ssh_user');
        $sshPw = $request->input('ssh_pw');

        $sshConnection = ssh2_connect($ip, 22);

        try {
            ssh2_auth_password($sshConnection, $sshUser, $sshPw);
            ssh2_exec($sshConnection, 'if ! [ -d "~/.ssh" ]; then mkdir ~/.ssh; touch ~/.ssh/authorized_keys; fi');

        }
        catch (\ErrorException $e)
        {
            return redirect()->back()->withErrors(['make sure ssh user and password are correct']);
        }
        Log::info('creating server inside ServersController');
       try {
           $server = $customForge->createServer([
               "provider" => "custom",
               "name" => $request->input('name'),
               "size" => "2",
               "php_version" => "php71",
               "ip_address" => $request->input('ip'),
               "private_ip_address" => $request->input('ip')
           ]);
       }
       catch (ValidationException $e)
       {
           return redirect()->back()->withErrors(['check your server name and data are valid']);
       }
        Server::create([
            'name' => $request->input('name'),
            'forge_server_id' => $server['server']['id'],
            'ip' => $ip
        ]);
        $provisionCommand = str_replace(' bash','; bash',$server['provision_command']);

        exec('ssh-keygen -f "/home/forge/.ssh/known_hosts" -R '.$ip);

        exec("cat ~/.ssh/id_rsa.pub | sshpass -p $sshPw ssh -oStrictHostKeyChecking=no $sshUser@$ip 'cat >> .ssh/authorized_keys'");

        file_put_contents(base_path() .'/Envoy.blade.php' ,$this->getEnvoyContent($provisionCommand,"$sshUser@$ip"));

        exec("cd /home/forge/".config('fannics-portal.domain.main')."/ && ". '~/.config/composer/vendor/bin/envoy run provision > /dev/null &');

        $job = (new RunRecipe($server['server']['id']))
            ->delay(Carbon::now()->addMinutes(20));

        dispatch($job);

        return redirect()->route('servers')->with(['success' => true , 'message' => 'Server Created Please Wait 15 Minutes To Be Operational']);;
    }

    private function getEnvoyContent($provisionCommand,$ssh)
    {
        $server = '@servers([\'web\' => [\''.$ssh.'\']])' . "\n\n";

        $startTask = '@task(\'provision\', [\'on\' => \'web\'])' ."\n";

        $endTask = "\n@endtask\n";

        return $server . $startTask . $provisionCommand . $endTask;
    }

    public function checkStatus(Request $request)
    {
        $response = [];

        foreach ($request->input('ids') as $server)
        {
                $status = 'Created';

                $dbServer = Server::where('forge_server_id',$server['id'])->first();

                if (is_null($dbServer))
                {
                    $response [] = [ 'id' => $server['id'] , 'status' => 'deleted'];
                    Server::where('forge_server_id',$server['id'])->delete();
                    Site::where('server_id',$server['id'])->delete();

                    return response()->json($response);
                }

                if ($dbServer->steps == Server::OPERATIONAL)
                {
                    $status = 'Operational';
                 }

                $response [] = ['id' => $server['id'] , 'status' => $status];

                return response()->json($response);
        }
    }
}

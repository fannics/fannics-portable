<?php
/**
 * Created by PhpStorm.
 * User: te7a
 * Date: 19/07/17
 * Time: 04:17 م
 */

namespace App\Console;

use App\Site;
use App\Server;
use Forge;
use Log;

class Servers
{
    public function update()
    {
        Log::info('running Server update scheduled hitting the api');
        $servers = collect(Forge::servers());

        $serversIds = $servers->pluck('id')->toArray();

        Server::whereNotIn('forge_server_id',$serversIds)->delete();
        Site::whereNotIn('server_id',$serversIds)->delete();

        foreach ($servers as $server)
        {
            $dbServer = Server::where('forge_server_id',$server->id)->first();

            if (is_null($dbServer))
            {
                Server::create([
                    'name' => $server->name ,
                    'forge_server_id' => $server->id,
                    'ip' => $server->ipAddress
                ]);

                continue;
            }

            $status = Server::CREATED;

            if ($server->isReady)
            {
                $status = Server::OPERATIONAL;
                Log::info('hit the api getting all daemons inside servers console');
                $daemons = Forge::daemons($server->id);

                if (count($daemons) >= 1)
                {
                    foreach ($daemons as $daemon)
                    {
                        Log::info('hit the api restart daemon inside servers console');
                        $daemon->restart($wait = false);
                    }

                }
                else
                {
                    Log::info('hit the api create daemon inside servers console ');
                    Forge::createDaemon($server->id, ['user' => 'forge' , 'command' => 'thumbor'], $wait = false);

                }
            }


            $dbServer->update(['name' => $server->name , 'steps' => $status]);
        }
    }


}
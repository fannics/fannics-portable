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
use Themsaid\Forge\Exceptions\NotFoundException;
use App\Helpers\DeploymentHelper;

class Sites
{

    public function update()
    {
        Log::info('running the update Site schedule and getting all servers inside Sites console');
        $servers = Server::all();

        foreach ($servers as $server) {
            try {
                Log::info('hit the api and getting all servers sites inside Sites console');
                $sites = collect(Forge::sites($server->forge_server_id));

                Site::whereNotIn('forge_site_id', $sites->pluck('id')->toArray())
                    ->where('server_id', $server->forge_server_id)
                    ->delete();

                foreach ($sites as $site) {
                    Log::info('dispatching update site');
                   $this->updateSite($site);
                }

            } catch (NotFoundException $e) {
                Server::where('forge_server_id',$server->forge_server_id)->delete();
            }
            catch (\Exception $e)
            {
                Server::where('forge_server_id',$server->forge_server_id)->delete();
            }
        }

        Log::info('finish the for loop Sites console');
    }

    private function updateSite($siteApi)
    {
        try {
            Log::info('inside update site job');
            if (!in_array($siteApi->name ,array_values(config('fannics-portal.domains'))))
            {
                $dbSite = Site::where('forge_site_id',$siteApi->id)->first();

                if ( is_null($dbSite))
                {
                    return 0;
                }

                $step = ($siteApi->repositoryStatus == 'installed' && $siteApi->repositoryBranch =='develop-new' && $siteApi->status == 'installed' && $dbSite->step_two_filled) ? Site::OPERATIONAL  : (($dbSite->step_two_filled) ? Site::CREATED : Site::INSTALLING)  ;

                if (is_null($siteApi->repositoryStatus) || is_null($siteApi->repositoryBranch))
                {
                    $step = Site::INSTALLING;
                }

                if ($step == Site::OPERATIONAL && !$dbSite->updated_deployment_script)
                {
                    Log::info('hit the api and updating deployment script inside UpdateSite job');

                    $siteApi->updateDeploymentScript(DeploymentHelper::getScript($dbSite),$wait=false);
                }

                $dbSite->update([
                    'domain' => $siteApi->name,
                    'steps' => $step,
                    'updated_deployment_script' => 1
                ]);
            }

        }
        catch (\ErrorException $e)
        {
            Log::error($e->getMessage());
        }
    }

}
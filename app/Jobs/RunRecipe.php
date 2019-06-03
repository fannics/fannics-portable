<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Forge;
use App\Server;
use Log;
use Themsaid\Forge\Exceptions\NotFoundException;

class RunRecipe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $serverId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($serverId)
    {
        $this->serverId = $serverId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('getting all server sites in RunRecipe job');

        try {

            $allSites = Forge::sites($this->serverId);

            foreach ($allSites as $site)
            {
                if ($site->name == 'default')
                {
                    Log::info('deleting default site from server in RunRecipe job');
                    Forge::deleteSite($this->serverId,$site->id);
                }
            }

            Log::info('running recipe inside RunRecipe job');
            Forge::runRecipe(10888,['servers' => [$this->serverId]]);

            Server::where('forge_server_id',$this->serverId)->update([
                'steps' => Server::OPERATIONAL
            ]);
        }
        catch (NotFoundException $exception)
        {
            Log::info('trying to find servers sites , or running recipe on non exist server in RunRecipe.php');
        }
    }
}

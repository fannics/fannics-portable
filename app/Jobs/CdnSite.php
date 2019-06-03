<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Forge;
use App\Site;
use Log;
use Themsaid\Forge\Exceptions\NotFoundException;
use Themsaid\Forge\Exceptions\ValidationException;

class CdnSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected  $serverId;
    protected  $siteDbId;
    protected $cdnDomain;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($serverId,$siteDbId,$cdnDomain)
    {
        $this->serverId = $serverId;
        $this->siteDbId = $siteDbId;
        $this->cdnDomain = $cdnDomain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('creating cdn and hitting api inside CdnSite job');
            $cdnSite = Forge::createSite($this->serverId, [
                'domain' => $this->cdnDomain,
                'project_type' => 'php',
                'directory' => '/public'
            ], $wait = true);

            Site::where('id', $this->siteDbId)->update(['cdn_forge_id' => $cdnSite->id]);

            Log::info('getting site nginx file inside CdnSite job');
            //update cdn site nginx configuration
            $nginxConfig = Forge::siteNginxFile($cdnSite->serverId, $cdnSite->id);

            $proxy = "location ~* / {\n
                         proxy_set_header X-Real-IP \$remote_addr;\n
                         proxy_set_header HOST \$http_host;\n
                         proxy_set_header X-NginX-Proxy true;\n
                         proxy_pass http://thumbor;\n
                         proxy_redirect off;\n
                        }\n";

            $nginxConfig = substr_replace($nginxConfig, $proxy, 2038, 0);

            Log::info('getting all sites inside CdnSite job');
            $allSites = Forge::sites($this->serverId);

            if (count($allSites) <= 3) {
                $includeThumborFlag = true;

                foreach ($allSites as $site) {
                    Log::info('getting all sites nginx files inside CdnSite job');
                    $tempConf = Forge::siteNginxFile($site->serverId, $site->id);

                    if (str_contains($tempConf, 'thumbor')) {
                        $includeThumborFlag = false;
                    }
                }

                if ($includeThumborFlag) {
                    $nginxConfig .= "upstream thumbor  {\n
                         server 127.0.0.1:8000;\n
                         server 127.0.0.1:8001;\n
                         server 127.0.0.1:8002;\n
                         server 127.0.0.1:8003;\n
                        server 127.0.0.1:8888;\n
                        }\n";

                }
            }
            Log::info('updating site nginx  inside CdnSite job');
            Forge::updateSiteNginxFile($cdnSite->serverId, $cdnSite->id, $nginxConfig);
        }
        catch (NotFoundException $exception)
        {
            Log::error('not found exception CdnSite job');
        }

        catch (ValidationException $exception)
        {
            Log::error('validation exception in CdnSite job' . $this->cdnDomain . ' ' . $this->serverId);
        }
    }
}

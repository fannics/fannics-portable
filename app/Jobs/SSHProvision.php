<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SSHProvision implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ip;
    protected $sshUser;
    protected $sshPw;
    protected $provisionCommand;

    public function __construct($ip,$sshUser,$sshPw,$provisionCommand)
    {
        $this->ip = $ip;
        $this->sshUser = $sshUser;
        $this->sshPw = $sshPw;
        $this->provisionCommand = $provisionCommand;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sshConnection = ssh2_connect($this->ip, 22);
        ssh2_auth_password($sshConnection, $this->sshUser, $this->sshPw);
        ssh2_exec($sshConnection, $this->provisionCommand);
    }
}

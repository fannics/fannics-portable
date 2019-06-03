<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ForgeProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        app()->bind('Forge',function(){
            return new \Themsaid\Forge\Forge(config('forge.token'));
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;
use App\Version;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('domain', function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/m", $value);
        });

        Validator::extend('without_spaces', function($attr, $value){
            return preg_match('/^\S*$/u', $value);
        });

        Validator::extend('version_number', function ($attribute, $value, $parameters, $validator) {

            $lastVersionNumber = Version::orderByDesc('number')->first();

            $passed = is_null($lastVersionNumber) ? true : $value > $lastVersionNumber->number;

            if (!$passed)
            {
                $validator->addReplacer('version_number', function($message, $attribute, $rule, $parameters) use($lastVersionNumber){
                   return  str_replace(':value',$lastVersionNumber->number ,$message );

                });
            }

           return $passed;

        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

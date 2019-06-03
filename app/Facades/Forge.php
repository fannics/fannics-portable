<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Forge extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Forge';
    }

}
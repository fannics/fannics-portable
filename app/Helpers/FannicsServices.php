<?php

namespace App\Helpers;

use App\Version;

class FannicsServices {

    public static function newVersionServices()
    {
        $services = [];

        foreach (config('fannics-services-new-version-names') as $code => $labelName)
        {
            if (Version::whereRaw('services like \'%"code":"'.$code.'"%\' LIMIT 1')->get()->isEmpty())
            {
                    $services [$code] = $labelName;
            }

        }

        return $services;
    }
}
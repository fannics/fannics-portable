<?php

namespace App\Helpers;

class DeploymentHelper
{
    public static function getScript($siteDb)
    {
        $branch = "git pull origin develop-new";
        $commit = "git reset --hard " . $siteDb->version->commit;
        $composer = "composer install --no-interaction --prefer-dist --optimize-autoloader";
        $phpService = 'echo "" | sudo -S service php7.1-fpm reload';
        $migration = "if [ -f artisan ]\nthen\n   php artisan migrate --force --seed\n php artisan cache:clear\nphp artisan clear-compiled\nphp artisan config:clear\nphp artisan route:clear\nphp artisan view:clear\nphp artisan key:generate --force\nfi";

        return "cd /home/forge/" . $siteDb->domain ."\n" .$branch . "\n".$commit . "\n" .$composer . "\n" . $phpService ."\n" .$migration ."\n";

    }

    public static function getPortalScript()
    {
        $branch = "git pull origin master";

        $composer = "composer install --no-interaction --prefer-dist --optimize-autoloader";
        $phpService = 'echo "" | sudo -S service php7.1-fpm reload';
        $migration = "if [ -f artisan ]\nthen\n   php artisan migrate --force \n php artisan queue:restart\nfi";

        return "cd /home/forge/".config('fannics-portal.domain.main') ."\n" .$branch . "\n" .$composer . "\n" . $phpService ."\n" .$migration ."\n";

    }
}
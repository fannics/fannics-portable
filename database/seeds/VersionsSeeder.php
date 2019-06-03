<?php

use Illuminate\Database\Seeder;
use App\Version;
use App\Site;
use App\SiteServices;

class VersionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Version::where('number','1.0')->first() == null)
        {
            $version =  Version::create([
                'name' => 'First Version',
                'commit' => '46efe13b63d2eea6b033f1b0393a07211aa6ae73',
                'number' => 1.0,
                'services' => json_encode([['code' => 'rollbar']])
            ]);

            Site::whereNull('version_id')->update(['version_id' => $version->id]);

            foreach (Site::all() as $site)
            {
                SiteServices::create([
                    'site_id' => $site->id,
                    'services' => json_encode(['rollbar' => ['token' => 'babb84ff188d4818b3b9fd05d06e975e']])
                ]);
            }

        }
    }
}

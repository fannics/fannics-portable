<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    const INSTALLING = 1;
    const CREATED = 2;
    const OPERATIONAL = 3;
    const UPDATING = 4;

    protected $table = 'sites';
    protected $fillable = ['name','forge_site_id','server_id','steps','domain','protocol','version_id','step_two_filled',
                            'cdn_forge_id','cdn_domain','cdn_protocol','updated_deployment_script'];

    public function server()
    {
        return $this->belongsTo(Server::class,'server_id','forge_server_id');
    }

    public function services()
    {
        return $this->hasOne(SiteServices::class);
    }

    public function setProtocolAttribute($value)
    {
        $this->attributes['protocol'] = ($value ? 'https' : 'http');
    }

    public function setCdnProtocolAttribute($value)
    {
        $this->attributes['cdn_protocol'] = ($value ? 'https' : 'http');
    }

    public function version()
    {
        return $this->belongsTo(Version::class);
    }

    public function definedServices()
    {
        return array_keys(json_decode($this->services->services,true));
    }

    public function getCurrentStatus()
    {
        $allSteps = [  Site::INSTALLING => 'Step 2 Not Created', Site::CREATED => 'Created' , Site::OPERATIONAL => 'Operational' , Site::UPDATING => 'Updating'];

        foreach ($allSteps as $step => $value)
        {
            if ($this->steps ==  $step)
            {
                return $value;
            }
        }
    }
}

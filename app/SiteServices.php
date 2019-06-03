<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiteServices extends Model
{
    protected $table = 'site_services';

    protected $fillable = ['site_id','services'];
}

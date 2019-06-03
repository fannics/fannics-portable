<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    const CREATED = 1;
    const OPERATIONAL = 2;

    protected $fillable=['name' , 'forge_server_id' , 'steps','ip'];
}

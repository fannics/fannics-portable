<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $fillable = ['number','name','services','commit'];

    public function getNumberAttribute($value)
    {
        return number_format($value,1);
    }

    public static function latest()
    {
        return self::orderByDesc('number')->first();
    }

    public static function allServices()
    {
        $temp = [];
        foreach (Version::all()  as $version)
        {
            foreach (json_decode($version->services,true) as $service)
            {
                $temp[] = $service['code'];
            }
        }
        return $temp;
    }

    public static function after($versionNumber)
    {
        return self::where('number','>',$versionNumber)->get();
    }

    public function services()
    {
        $temp = [];
        $previousServices = [];

        foreach (Version::where('number','<',$this->number)->get() as $version)
        {
            foreach (json_decode($version->services,true) as $service)
            {
                $previousServices [] = $service['code'];
            }
        }


        foreach (json_decode($this->services,true) as $service)
        {
            $temp[] = $service['code'];
        }

        return array_merge($temp,$previousServices);
    }

    public function configNotes()
    {
        return config('fannics-versions-notes.' . $this->name);
    }
}

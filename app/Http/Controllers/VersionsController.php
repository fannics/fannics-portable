<?php

namespace App\Http\Controllers;

use App\Helpers\FannicsServices;
use App\Http\Requests\StoreVersionRequest;
use App\Version;

class VersionsController extends Controller
{
    public function index()
    {
        $versions = Version::all();

        return view('versions.index',compact('versions'));
    }

    public function create()
    {
        $services = FannicsServices::newVersionServices();

        return view('versions.create',compact('services'));
    }

    public function store(StoreVersionRequest $request)
    {
        $services = !empty($request->input('services')) ? array_keys($request->input('services')) : [];

        $services = $this->mapServicesWithCode($services);

        Version::create([
            'name' => $request->input('name'),
            'number' => $request->input('number'),
            'commit' => $request->input('commit'),
            'services' => $services
        ]);

        return redirect()->route('versions')->with(['success' => true , 'message' => 'Version Created Successfully']);
    }

    private function mapServicesWithCode($services)
    {
        $temp = [];

        foreach ($services as $service)
        {
            $temp [] = ['code' => $service];
        }

        return json_encode($temp);
    }
}

@extends('layouts.app')

@section('content')
    <div class="row">

        <div class="panel panel-primary">
            <div class="panel-heading">Versions <span class="pull-right"><a class="btn btn-xs btn-default" href="{{route('versions.create')}}"> Create Version</a></span></div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Commit</th>
                    </tr>
                    </thead>

                    @foreach($versions as $version)
                        <tr>
                            <td>{{$version->name}}</td>
                            <td>{{$version->number}}</td>
                            <td>{{$version->commit}}</td>
                        </tr>

                    @endforeach

                </table>
            </div>
        </div>

    </div>
@endsection

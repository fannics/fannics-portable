@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Version</div>
                <div class="panel-body">
                    <form class="form-horizontal"  method="POST" action="{{route('versions.store')}}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="number" class="col-md-4 control-label">Number</label>

                            <div class="col-md-6">
                                <input id="number" type="text" class="form-control" name="number" value="{{ old('number') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="commit" class="col-md-4 control-label">Commit Code</label>

                            <div class="col-md-6">
                                <input id="commit" type="text" class="form-control" name="commit" value="{{ old('commit') }}" required autofocus>

                            </div>
                        </div>

                        @if(!empty($services))
                            @foreach($services as $name => $label)
                                <div class="form-group" @if(!$loop->last) style="margin-bottom: 1px;" @endif>

                                    <div class="col-md-4 control-label">
                                        <input type="checkbox"   name="services[{{$name}}]">
                                    </div>

                                    <div class="control-label" style="text-align: left">
                                            <label class="col-md-6 ">{{$label}}</label>
                                    </div>

                                </div>
                            @endforeach
                        @endif


                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Create
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

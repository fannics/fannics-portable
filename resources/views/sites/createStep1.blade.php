@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Site Step 1</div>
                <div class="panel-body">
                    <form class="form-horizontal"  method="POST" action="{{route('sites.store.step1')}}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Domain</label>

                            <div class="col-md-6">
                                <input id="domain" type="text" class="form-control" name="domain" value="{{ old('domain') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cdn_domain" class="col-md-4 control-label">CDN Address</label>

                            <div class="col-md-6">
                                <input id="cdn_domain" type="text" class="form-control" name="cdn_domain" value="{{ old('cdn_domain') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="server" class="col-md-4 control-label">Server</label>

                            <div class="col-md-6">
                                <select  class="form-control" name="server">
                                    @foreach($servers as $server)
                                        <option value="{{$server->forge_server_id}}">{{$server->name}}</option>
                                        @endforeach
                                </select>
                              

                            </div>
                        </div>

                        <div class="form-group"  >

                            <div class="col-md-4 control-label">
                                <input type="checkbox"   name="protocol">
                            </div>

                            <div class="control-label" style="text-align: left">
                                <label class="col-md-6 ">Secure (HTTPS)</label>
                            </div>

                        </div>

                        <div class="form-group"  >

                            <div class="col-md-4 control-label">
                                <input type="checkbox"   name="cdn_protocol">
                            </div>

                            <div class="control-label" style="text-align: left">
                                <label class="col-md-6 ">Secure CDN (HTTPS)</label>
                            </div>

                        </div>


                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Next
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>





@endsection
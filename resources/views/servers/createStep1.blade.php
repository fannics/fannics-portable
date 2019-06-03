@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Server Step 1</div>
                <div class="panel-body">
                    <form class="form-horizontal"  method="POST" action="{{route('servers.store.step1')}}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Name</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ip" class="col-md-4 control-label">IP</label>

                            <div class="col-md-6">
                                <input id="ip" type="text" class="form-control" name="ip" value="{{ old('ip') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phpVersion" class="col-md-4 control-label">PHP Version</label>

                            <div class="col-md-6">
                                <input id="phpVersion" type="text" class="form-control"  value="7.1" required disabled>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ssh_user" class="col-md-4 control-label">SSH User</label>

                            <div class="col-md-6">
                                <input id="ssh_user" type="text" class="form-control" name="ssh_user" value="{{ old('ssh_user') }}" required autofocus>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="ssh_pw" class="col-md-4 control-label">SSH Password</label>

                            <div class="col-md-6">
                                <input id="ssh_pw" type="text" class="form-control" name="ssh_pw" value="{{ old('ssh_pw') }}" required autofocus>

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
@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div id="panel" class="panel @if($forgeSite->repositoryStatus != 'installed')panel-danger @else panel-success @endif">
                <div id="panelMessage" class="panel-heading ">@if($forgeSite->repositoryStatus != 'installed') Create Site Step 2 - Getting ready, please wait  &nbsp; <i class="fa fa-spinner fa-spin" style="font-size:18px"></i>@else Continue creating the site - Step 2 @endif</div>
                <div class="panel-body">
                    <form id="secondStepForm" class="form-horizontal"  method="POST" action="{{route('sites.store.step2',$siteDb->id)}}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Database Name</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="db_name" value="{{old('db_name')}}" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label  class="col-md-4 control-label">Database User</label>

                            <div class="col-md-6">
                                <input type="text" class="form-control" name="db_user" value="{{old('db_user')}}" disabled>

                            </div>
                        </div>

                        <div class="form-group">
                            <label  class="col-md-4 control-label">Database User Password</label>

                            <div class="col-md-6">
                                <input class="form-control" type="password" name="db_pw" disabled>

                            </div>
                        </div>

                        @foreach($services as $code)
                            @php $serviceDetail = config('fannics-services.'.$code) ? : []; @endphp
                            @foreach($serviceDetail as $name => $label)
                                <div class="form-group">
                                    <label  class="col-md-4 control-label">{{$label}}</label>

                                    <div class="col-md-6">
                                        <input class="form-control" type="text" name="services[{{$code}}][{{$name}}]" value="{{old('services.'.$code.'.' .$name)}}" disabled>

                                    </div>
                                </div>

                           @endforeach
                        @endforeach




                        <div class="form-group">
                            <label  class="col-md-4 control-label">Admin Email</label>

                            <div class="col-md-6">
                                <input class="form-control" type="text" name="admin_email" value="{{old('admin_email')}}" disabled>

                            </div>
                        </div>

                        <div class="form-group">
                            <label  class="col-md-4 control-label">Admin Password</label>

                            <div class="col-md-6">
                                <input class="form-control" type="password" name="admin_pw" disabled>
                            </div>
                        </div>


                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary" disabled>
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

@section('scripts')

    @if($forgeSite->repositoryStatus == 'installed')
        <script type="text/javascript">
            $("#secondStepForm :input").attr("disabled", false);

        </script>
    @endif
    <script type="text/javascript">

        $(document).ready(function(){
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                }
            });
           var intervalID =  setInterval(checkSiteInstalled, 12000);

            function checkSiteInstalled () {
                $.ajax({
                    'url': '{{route('sites.check-installed',$siteDb->id)}}',
                    'method': 'POST',
                    'success': function (response) {
                        var messages = response.messages;

                        if (response.status == 'success') {
                            $('#panel').attr('class','panel panel-success');
                            $('#panelMessage').text( messages[0]);
                            $("#secondStepForm :input").attr("disabled", false);
                            clearInterval(intervalID);
                        }


                    }
                });
            }

        });
    </script>

    @endsection
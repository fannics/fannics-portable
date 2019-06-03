@extends('layouts.app')

@section('content')
    <div class="row">

        <div class="panel panel-primary">
            <div class="panel-heading">Sites <span class="pull-right"><a class="btn btn-xs btn-default" href="{{route('sites.create.step1')}}"> Create Site</a></span></div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Domain</th>
                        <th>Server</th>
                        <th>Status</th>
                        <th>Version</th>
                        <th>Version Num</th>
                    </tr>
                    </thead>

                    @foreach($sites as $site)
                        <tr  siteCheck="siteCheck-{{$site->forge_site_id}}" id="{{$site->forge_site_id}}" serverId="{{$site->server_id}}" >
                            @if($site->steps == App\Site::INSTALLING)<td><a href="{{route('sites.create.step2',$site->id)}}">{{$site->name}}</a></td>  @endif
                            @if($site->steps == App\Site::CREATED) <td>{{$site->name}}</td>@endif
                            @if ( $site->steps == App\Site::OPERATIONAL) <td><a href="{{route('sites.version.edit',$site->id)}}">{{$site->name}}</a></td> @endif

                            <td><a href="{{$site->protocol}}://{{$site->domain}}" target="_blank">{{$site->domain}}</a></td>

                            <td>{{$site->server->name}}</td>

                            <td siteStatus="siteStatus-{{$site->forge_site_id}}">
                                @if($site->steps == App\Site::INSTALLING) Step 2 Not Created @endif
                                @if($site->steps == App\Site::CREATED) Created @endif
                                @if($site->steps == App\Site::OPERATIONAL) Operational @endif
                                @if($site->steps == App\Site::UPDATING) Updating @endif

                            </td>
                            <td>{{$site->version->name}}</td>
                            <td>{{$site->version->number}}</td>
                        </tr>

                    @endforeach

                </table>
            </div>
        </div>

    </div>
    @endsection

@section('scripts')

<script type="text/javascript">

    $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            }
        });

        var intervalID =  setInterval(checkSitesStatus, 15000);

        function checkSitesStatus () {

            var ids = [];

            $('[siteCheck^=siteCheck-]').each(function(){
                ids.push({'id' : $(this).attr('id') , 'serverId' : $(this).attr('serverId')});
            });

            if (ids.length <= 0)
            {
                clearInterval(intervalID);

                return 0;
            }

            $.ajax({
                'url': '{{route('sites.check-ids-status')}}',
                'data' : {'ids' : ids},
                'method': 'POST',
                'success': function (response) {
                    for(var i in response)
                    {
                        if (response[i].status == 'deleted' || response[i].status == 'Operational')
                        {
                            $('[siteCheck^=siteCheck-'+(response[i].id)+']').removeAttr('siteCheck');
                        }

                        $('[siteStatus^=siteStatus-'+ response[i].id +']').text(response[i].status);
                    }
                }
            });
        }

    });

</script>

@endsection
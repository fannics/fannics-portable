@extends('layouts.app')

@section('content')
    <div class="row">

        <div class="panel panel-primary">
            <div class="panel-heading">Servers <span class="pull-right"><a class="btn btn-xs btn-default" href="{{route('servers.create.step1')}}"> Create Server</a></span> </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>IP</th>
                        <th>Status</th>
                    </tr>
                    </thead>

                    @foreach($servers as $server)
                        <tr @if($server->steps == 1) serverCheck="serverCheck-{{$server->forge_server_id}}" id="{{$server->forge_server_id}}" @endif>
                            <td>{{$server->name}}</td>
                            <td>{{$server->ip}}</td>
                            <td serverStatus="serverStatus-{{$server->forge_server_id}}"> @if($server->steps == 1)Created  @else Operational @endif</td>
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

            var intervalID =  setInterval(checkServersStatus, 120000);

            function checkServersStatus () {

                var ids = [];

                $('[serverCheck^=serverCheck-]').each(function(){
                    ids.push({'id' : $(this).attr('id')});
                });

                if (ids.length <= 0)
                {
                    clearInterval(intervalID);

                    return 0;
                }

                $.ajax({
                    'url': '{{route('servers.check-status')}}',
                    'data' : {'ids' : ids},
                    'method': 'POST',
                    'success': function (response) {
                        for(var i in response)
                        {
                            if (response[i].status == 'deleted' || response[i].status == 'Operational')
                            {
                                $('[serverCheck^=serverCheck-'+(response[i].id)+']').removeAttr('serverCheck');
                            }

                            $('[serverStatus^=serverStatus-'+ response[i].id +']').text(response[i].status);
                        }
                    }
                });
            }

        });

    </script>

@endsection
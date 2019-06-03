@extends('layouts.app')

@section('content')


    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Update Site From Version {{$site->version->number}}</div>
                <div class="panel-body">
                    @if($versions->isEmpty())
                        Site Already Updated
                    @endif
                    @if(!$versions->isEmpty())
                    <form class="form-horizontal"  method="POST" action="{{route('sites.version.update',$site->id)}}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <label for="name" class="col-md-4 control-label">Versions</label>

                            <div class="col-md-6">
                                <select id="versions" class="form-control" name="version" required>
                                    @foreach($versions as $version)
                                        <option value="{{$version->id}}">{{$version->number}}</option>
                                    @endforeach
                                </select>

                                <br>
                                <div id="notes" @if (empty($firstVersionNote) ) style="display: none"@endif  class="alert alert-warning">
                                    <ul>
                                        <li>@if( !empty($firstVersionNote)){{$firstVersionNote}}@endif </li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                        <div id="services">
                            @if(!empty($firstServices))
                                @foreach($firstServices as $code)
                                    @php $serviceDetail = config('fannics-services.'.$code) ? : []; @endphp
                                    @foreach($serviceDetail as $name => $label)
                                        <div class="form-group">
                                            <label  class="col-md-4 control-label">{{$label}}</label>

                                            <div class="col-md-6">
                                                <input class="form-control" type="text" name="services[{{$code}}][{{$name}}]" value="{{old('services.'.$code.'.' .$name)}}" >

                                            </div>
                                        </div>

                                    @endforeach
                                @endforeach

                                @endif
                        </div>

                        <div id="newServiceClone" style="display: none;" class="form-group">
                            <label   class="serviceLabel col-md-4 control-label"> </label>

                            <div class="col-md-6">
                                <input   type="text" class="form-control serviceName"     >

                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                     @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

    <script>
        $(document).ready(function(){

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                }
            });


            $('#versions').on('change',function(){
                var version = $(this).val();
                $.ajax({
                    'url' : '{{route('sites.version.services')}}',
                    'method' : 'POST',
                    'data' : {'siteId': '{{$site->id}}' , 'versionId' :version   },
                    'success' : function(response){
                        $('#services').html('');
                        $('#notes').hide().html('');

                        var services = response.services;
                        var notes = response.notes;

                        for (var index in services)
                        {
                           var clonedService = $('#newServiceClone').clone();

                            clonedService.attr('id','');
                            clonedService.attr('style','');
                            clonedService.find('.serviceLabel').text(services[index].label);
                            clonedService.find('.serviceName').attr('name','services['+services[index].code + ']' + '[' + services[index].name + ']');

                            $('#services').append(clonedService);
                        }

                        if (notes)
                        {
                            $('#notes').show().html('<ul><li>'+notes+'</li></ul>');
                        }
                    }
                });
            });
        });


    </script>

@endsection
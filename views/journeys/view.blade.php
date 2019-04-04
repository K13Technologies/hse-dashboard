@extends('webApp::layouts.withNav')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/journey-management/journey-details.css') }}}" rel="stylesheet">
@stop

@section('content')
<div class='container'>              
    <div class="row"> 
        <div class="col-md-6">
           @if ($journey instanceof JourneyV2)
           <?php $terminated = $journey->terminatedIncorrectly(); ?>
           <div class="list-container" >
                <div class="list-container-header">
                  <div class="list-component-container">
                      <span class="list-component">Journey Details:</span>
                      
                  </div>
                    <div class="right-header-container pull-right">
                          <div class="right-header-label"> Date </div>
                          <div id="inspection-date-container" class="right-header-text"> 
                                <span title="{{ $journey->created_at }}"> 
                                    {{ WKSSDate::display($journey->ts_created, $journey->created_at) }} 
                                </span> 
                          </div>
                    </div>
                </div>
                <div class="list-container-body">
                    <div id="journey-details-container">
                       <div class="row-fluid" id="worker-container">
                            <div class="section-header-label"> Performed by {{ $journey->addedBy->first_name.' '.$journey->addedBy->last_name }} </div>
                                <div class="span6" style="margin-left:0px;">
                                <div class="content-label">Contact by phone</div>
                                        <div class="content-description">{{ $journey->addedBy->cell_phone }} </div>
                                </div>
                                <div class="span6">
                                <div class="content-label">Contact by radio</div>
                                        <div class="content-description">  {{ implode('<br/> ',array_pluck($journey->addedBy->company->radioStations,'value')) }}</div>
                                </div>
                        </div>
                        <div class="journey-section-header">
                            <div class="journey-header-label"> Starting point </div>
                            <div class="journey-details-content row-fluid" id="fromAddress">
                                <div class="span6">
                                    <div class="content-label">Starting location </div>
                                    <div class="content-description"> {{ $journey->journeyFrom->title }} </div>
                                </div>
                                <div class="span6">
                                    <div class="content-label">Started at</div>
                                    <div class="content-description">
                                        <span title="{{ $journey->started_at }}"> 
                                            {{ WKSSDate::display($journey->ts_started, $journey->started_at) }} 
                                        </span> 
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <?php $i=0; ?>
                        @foreach ($journey->endpoints as $endp)
                        <div class="journey-section-header tabbed">
                            <div class="journey-header-label"> Checkpoint - {{ $endp->location->title }}  </div>
                            <div class="journey-details-content row-fluid">
                                @if ($endp->arrived)
                                <div class="span6">
                                    <div class="content-label">Status</div>
                                    <div class="content-description text-success"> Arrived </div>
                                </div>
                                <div class="span6">
                                    <div class="content-label">Arrival time</div>
                                    <div class="content-description">
                                        <span title="{{ $endp->arrived }}"> 
                                            {{ WKSSDate::display($endp->ts_arrived, $endp->arrived) }} 
                                        </span> 
                                    </div>
                                </div>
                                @else
                                    @if ($i==0)
                                        @if ($journey->finished_at)
                                            <div class="span6">
                                        @else
                                            <div class="span6 here-marker">
                                        @endif
                                            <div class="content-label">Status</div>
                                                    @if ($terminated)
                                                    <div class="text-error"> 
                                                        <b> Canceled </b>
                                                    @else
                                                    <div class="text-success"> 
                                                        <b> Enroute </b> 
                                                    @endif
                                            </div>
                                        </div>
                                        <div class="span6">
                                            <div class="content-label">Last checkin for this checkpoint</div>
                                            <div class="content-description">
                                                <?php $lastEndp = JourneyCheckinV2::lastCheckinForEndpoint($endp); ?>
                                                @if($lastEndp !==NULL )
                                                    <span title="{{ $lastEndp->created_at }}"> 
                                                        {{ WKSSDate::display($lastEndp->ts, $endp->created_at) }} 
                                                    </span> 
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="span6">
                                            <div class="content-label">Status</div>
                                            <div class="content-description text-success"> Pending </div>
                                        </div>
                                    @endif
                                 <?php $i++; ?>
                                @endif
                            </div>
                        </div>  
                        @endforeach
                        @if ($journey->finished_at)
                            <div class="journey-header-label"> 
                                @if ($terminated)
                                    Journey Canceled
                                @else
                                    Journey Complete
                                @endif
                            </div>
                            <div class="journey-details-content row-fluid">
                                  <div class="span6 <?php echo $terminated?'here-marker-red':'here-marker' ?>">
                                        <div class="content-label"> 
                                            @if ($terminated)
                                                Canceled at
                                            @else
                                                Completed at
                                            @endif
                                        </div>
                                        <div class="content-description"> 
                                            <span title="{{ $journey->finished_at }}"> 
                                                {{ WKSSDate::display($journey->ts_finished, $journey->finished_at) }} 
                                            </span> 
                                        </div>
                                    </div>
                                    <div class="span6">
                                        <div class="content-label">Last checkin for this journey</div>
                                        <div class="content-description">
                                            <?php $lastEndp = $journey->lastCheckin(); ?>
                                            @if($lastEndp !==NULL )
                                                <span title="{{ $lastEndp->created_at }}"> 
                                                    {{ WKSSDate::display($lastEndp->ts, $endp->created_at) }} 
                                                </span> 
                                            @else
                                                N/A
                                            @endif
                                        </div>
                                    </div>
                            </div>  
                        @endif
                        </div>
                    </div> 
                    <br/>
                </div>
            </div>
            @endif

            <div class="col-md-6">
                <div class="list-container" >
                    <div class="list-container-header">
                        <div class="list-component-container">
                            <span class="list-component">Map</span>   
                        </div>
                    </div>
                    <div class="list-container-body">
                        <div class="google-maps">
                            <iframe id="mapBox" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d4656384.074755432!2d-114.79513328320314!3d55.259760534002936!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sca!4v1426208220346" width="547" height="547" frameborder="0" style="border:0"></iframe>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- ROW !-->
</div>
@stop


@section('scripts')
  	@parent 
  	<script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
  	<script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  	<script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
  	<script src="{{{ asset('assets/js/wkss/journey-details.js') }}}"></script>
  	<script>
		// Prepares the table for niceness
		$(function(){
            // Disable for now because it looks bad
			//$("#journeyTable").dataTable();
		})
	</script>
@stop
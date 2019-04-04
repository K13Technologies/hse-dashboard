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

<!-- MODALS BEGIN !-->
    <div id="modalJourneyDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteJourneyLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 id="editJourneyLabel"> Delete Journey </h4>
                </div>
                <div class="modal-body">
                    {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteJourneyForm')) }}
                    
                        {{ Form::text('delete_journey_id','',array('class'=>'hide','id'=>'delete_journey_id')) }}
                    
                        <h5> Are you sure you want to delete this journey? </h5>

                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                    {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteJourneyButton')) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
<!-- MODALS END !-->


<div class='container'>
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif

    <div class="list-container">
        <div class="list-container-header">
          <div class="list-component-container">
              <span class="list-component">All Journeys</span>
          </div>
        </div>
        <div class="list-container-body">
             <table class="table-list table-hover dataTable" id="journeyTable">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Status</th>
                    <th class="action-column">Action</th>
                  </tr>
                </thead>
                <tbody id="adminsTable">
                  @foreach ($journeys as $j)
                    @if($j->deleted_at == NULL) 
                        <tr class="clickable-row"> 
                            <td onclick='window.document.location="{{ URL::to("journey-management/view",array($j->journey_id)) }}"'> 
                                {{ $j->addedBy->first_name.' '.$j->addedBy->last_name }} 
                            </td>
                            <td onclick='window.document.location="{{ URL::to("journey-management/view",array($j->journey_id)) }}"'> 
                                {{ $j->journeyFrom->title }} 
                            </td>
                            <td onclick='window.document.location="{{ URL::to("journey-management/view",array($j->journey_id)) }}"'>
                                @foreach ($j->endpoints as $endpoint)
                                    {{ $endpoint->location->title.'<br/>' }}
                                @endforeach 
                            </td>
                            <td onclick='window.document.location="{{ URL::to("journey-management/view",array($j->journey_id)) }}"'>
                                @if ($j->finished_at)
                                    @if($j->terminatedIncorrectly())
                                        <div class="text-error"> Journey Incomplete</div>
                                    @else
                                        <div class="text-info"> Journey Complete</div>
                                    @endif
                                @else
                                    @if($j->lastCheckin() instanceof JourneyCheckinV2)
                                        <span class="text-success" title="{{ $j->lastCheckin()->created_at }}">In progress</span>  
                                    @else
                                        <span class="text-success">In progress</span>  
                                    @endif
                                @endif 
                            </td>
                            <td class="action-column"> 
                                <a href="#" id="delete_{{ $j->journey_id}}" class="deleteJourneyLink"><i class="glyphicon glyphicon-trash"></i></a>
                            </td>
                        </tr>
                    @endif
                  @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div> 
@stop


@section('scripts')
  	@parent 
  	<script src="{{{ asset('assets/js/wkss/journey-management.js') }}}"></script>
@stop

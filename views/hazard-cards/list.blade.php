@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')


<!-- MODALS START -->
<div id="modalHazardDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteHazardLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteHazardLabel"> Delete Hazard Card </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteHazardForm')) }}
                <div class="modal-body">
                        {{ Form::text('delete_hazard_id','',array('class'=>'hide','id'=>'delete_hazard_id')) }}
                        <h5> Are you sure you want to delete this hazard card? </h5>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                    {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteHazardButton')) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- MODALS END -->

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
              <span class="list-component">All Hazard Cards</span>
          </div>
          <div class="pull-right">
               
          </div>
        </div>
        <div class="list-container-body">
             <table class="table-list table-hover dataTable" id="hazardTable">
                <thead>
                  <tr>
                    <th> Date</th>
                    <th> Title</th>
                    <th> Location</th>
                    <th> Action Req'd </th>
                    <th> Action Completed </th>
                    <th> LSD </th>
                    <th> Activity </th>
                    <th> Hazard Category</th>
                    <th class="action-column"> Actions </th>
                  </tr>
                </thead>
                <tbody id="adminsTable">
                    @foreach ($hazards as $h)
                        @if($h->deleted_at == NULL)
                            <tr class="clickable-row">
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'>
                                    {{ WKSSDate::display($h->ts, $h->created_at, WKSSDate::FORMAT_LIST) }} 
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'> 
                                    {{ $h->title }} 
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'> 
                                    {{ $h->site }} 
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'>
                                    @if ($h->corrective_action_applied==0 && $h->completed_on == NULL)
                                        Yes
                                    @else
                                        No
                                    @endif
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'>
                                    @if ($h->corrective_action_applied==0)
                                        @if ($h->completed_on != NULL)
                                            {{ $h->completed_on }}
                                        @else
                                            Outstanding
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'> 
                                    {{ $h->lsd }} 
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'> 
                                    @if ($h->hazard_activity)
                                        {{ $h->hazard_activity->activity_name }} 
                                    @endif
                                </td>
                                <td onclick='window.document.location="{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}"'>
                                    @if (count($h->hazard_categories)) 
                                        {{ implode(', ',array_pluck($h->hazard_categories->toArray(),'category_name')) }}
                                    @endif
                                </td>
                                <td class="action-column"> 
                                    <!--<a href='{{ URL::to("hazard-cards/view",array($h->hazard_id)) }}'><i class="icon-eye-open"></i></a>!-->
                                    <a href='{{ URL::to("hazard-cards/export",array($h->hazard_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                                    <a href="#" id="delete_{{ $h->hazard_id}}" class="deleteHazardLink"><i class="glyphicon glyphicon-trash"></i></a>
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
    <script src="{{{ asset('assets/js/wkss/hazard-card-management.js') }}}"></script>
@stop
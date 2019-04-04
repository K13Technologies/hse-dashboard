@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- MODALS START -->
<div id="modalIncidentDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteIncidentLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="editIncidentLabel"> Delete Incident </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteIncidentForm')) }}
                    {{ Form::text('delete_incident_id','',array('class'=>'hide','id'=>'delete_incident_id')) }}
                    <h5> Are you sure you want to delete this incident card? </h5>
            </div>
            <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">NO</button>
                    {{ Form::button('YES',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteIncidentButton')) }}
                {{ Form::close() }}
            </div>
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
                  <span class="list-component">All Incident Cards</span>
              </div>
              <div class="pull-right">
                   
              </div>
            </div>
            <div class="list-container-body">
                 <table class="table-list table-hover dataTable" id="incidentTable">
                    <thead>
                      <tr>
                        <th> Date </th>
                        <th> Title</th>
                        <th> Location</th>
                        <th> Action Req'd </th>
                        <th> Action Completed </th>
                        <th> LSD </th>
                        <th> Activity </th>
                        <th> Incident Category</th>
                        <th class="action-column"> Actions </th>
                      </tr>
                    </thead>
                    <tbody id="adminsTable">
                        @foreach ($incidents as $h)
                            @if($h->deleted_at == NULL)
                                <tr class="clickable-row">
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'> 
                                        {{ WKSSDate::display($h->ts,$h->created_at, WKSSDate::FORMAT_LIST) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'> 
                                        {{ $h->title }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'> 
                                        {{ $h->location }}, {{ $h->specific_area }}, {{ $h->road }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'>
                                        @if ($h->corrective_action_applied==0 && $h->completed_on == NULL)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'>
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
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'> 
                                        {{ $h->lsd }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'> 
                                        @if (count($h->incidentActivities))
                                            {{ implode('<br/> ',array_pluck($h->incidentActivities,'activity_name')) }}
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("incident-cards/view",array($h->incident_id)) }}"'>
                                        @if (count($h->incidentTypes)) 
                                            {{ implode('<br/>',array_pluck($h->incidentTypes,'type_name')) }}
                                        @endif
                                    </td>
                                    <td class="action-column"> 
                                        <!--<a href='{{ URL::to("incident-cards/view",array($h->incident_id)) }}'><i class="icon-eye-open"></i></a>!-->
                                        <a href='{{ URL::to("incident-cards/export",array($h->incident_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                                        <a href="#" id="delete_{{ $h->incident_id}}" class="deleteIncidentLink"><i class="glyphicon glyphicon-trash"></i></a>
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
    <script src="{{{ asset('assets/js/wkss/incident-management.js') }}}"></script>
@stop
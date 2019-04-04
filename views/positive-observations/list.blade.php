@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- MODALS BEGIN -->
<div id="modalObservationDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteObservationLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="editObservationLabel"> Delete Field Observation </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteObservationForm')) }}
                    {{ Form::text('delete_observation_id','',array('class'=>'hide','id'=>'delete_observation_id')) }}               
                    <h5> Are you sure you want to delete this field observation? </h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteObservationButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- MODALS END -->

    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif
                        
        

<div class='container'>
   <div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">All Field Observations</span>
              </div>
              <div class="pull-right">
                   
              </div>
            </div>
            <div class="list-container-body">
                 <table class="table-list table-hover dataTable" id="observationsTable">
                    <thead>
                      <tr>
                        <th> Date</th>
                        <th> Title</th>
                        <th> Location</th>
                        <th> Action Req'd </th>
                        <th> Action Completed </th>
                        <th> LSD </th>
                        <th> Activity </th>
                        <th> Categories </th>
                        <th class="action-column"> Actions </th>
                      </tr>
                    </thead>
                    <tbody id="adminsTable">
                        @foreach ($pos as $p)
                            @if($p->deleted_at == NULL)
                                <tr class="clickable-row">
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        {{ WKSSDate::display($p->ts, $p->created_at, WKSSDate::FORMAT_LIST) }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        {{ $p->title }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        {{ $p->site }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'>
                                        @if ($p->is_positive_observation==0 && $p->correct_on_site==0 && $p->completed_on == NULL)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'>
                                        @if ($p->is_positive_observation==0 && $p->correct_on_site==0)
                                            @if ($p->completed_on != NULL)
                                                {{ $p->completed_on }}
                                            @else
                                                Outstanding
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        {{ $p->lsd }} 
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        @if ($p->activity)
                                            {{ $p->activity->activity_name }} 
                                        @endif
                                    </td>
                                    <td onclick='window.document.location="{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}"'> 
                                        @if (count($p->positive_observation_categories)) 
                                            {{ implode(', ',array_pluck($p->positive_observation_categories->toArray(),'category_name')) }}
                                        @endif
                                    </td>
                                    <td class="action-column"> 
                                        <!--<a href='{{ URL::to("field-observations/view",array($p->positive_observation_id)) }}'><i class="icon-eye-open"></i></a>!-->
                                        <a href='{{ URL::to("field-observations/export",array($p->positive_observation_id)) }}'><i class="glyphicon glyphicon-file"></i></a>
                                        <a href="#" id="delete_{{ $p->positive_observation_id}}" class="deleteObservationLink"><i class="glyphicon glyphicon-trash"></i></a>
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
    <script src="{{{ asset('assets/js/wkss/positive-observation-management.js') }}}"></script>
@stop
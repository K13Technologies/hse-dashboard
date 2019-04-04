@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- MODALS START -->

<div id="modalVehicleDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteVehicleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="editVehicleLabel"> Delete Vehicle </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteVehicleForm')) }}
              <div class="modal-body">
                {{ Form::text('delete_vehicle_id','',array('class'=>'hide','id'=>'delete_vehicle_id')) }}
                <h5> Are you sure you want to delete this vehicle? </h5>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                  {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteVehicleButton')) }}
              </div>
            {{ Form::close() }}
        </div>
    </div>
</div>

<div id="modalVehicleEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editVehicleLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editVehicleLabel"> Edit Vehicle </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'editVehicleForm')) }}
                <div class="modal-body">
                  <div class="control-group hide">
                      {{ Form::label('edit_vehicle_id', 'Vehicle Id', array('class'=>'control-label')) }}
                      <div class="controls">
                          {{ Form::text('edit_vehicle_id','',array('class'=>'form-control')) }}
                      </div>
                  </div>  
                  <div class="control-group">
                      {{ Form::label('edit_license_plate', 'License Plate', array('class'=>'control-label')) }}
                      <div class="controls">
                          {{ Form::text('edit_license_plate','',array('class'=>'form-control')) }}
                      </div>
                  </div>  
                  <div class="control-group">
                      {{ Form::label('edit_vehicle_number', 'Vehicle Number', array('class'=>'control-label')) }}
                      <div class="controls">
                          {{ Form::text('edit_vehicle_number','',array('class'=>'form-control')) }}
                      </div>
                  </div>  
                   <div class="control-group">
                      {{ Form::label('edit_color', 'Color', array('class'=>'control-label')) }}
                      <div class="controls">
                          {{ Form::text('edit_color','',array('class'=>'form-control')) }}
                      </div>
                  </div>  
                  <div class="control-group">
                      {{ Form::label('edit_mileage', 'Mileage', array('class'=>'control-label')) }}
                      <div class="controls">
                          {{ Form::text('edit_mileage','',array('class'=>'form-control')) }}
                      </div>
                  </div>  
              </div>
              <div class="modal-footer">
                  <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">CLOSE</button>
                  {{ Form::button('SAVE',array('class'=>'btn btn-orange medium pull-right','id'=>'editVehicleButton')) }}
              </div>
            {{ Form::close() }}
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
                  <span class="list-component">All Vehicles</span>
              </div>
              <div class="pull-right">
                   
              </div>
            </div>
            <div class="list-container-body">
                 <table class="table-list table-hover dataTable" id="vehicleTable">
                    <thead>
                      <tr>
                        <th> Inspection </th>
                        <th> Action Req'd</th>
                        <th> Action Completed</th>
                        <th> License Plate </th>
                        <th> Vehicle Number </th>
                        <th> Mileage </th>
                        <th> Project </th>
                        <th class="action-column"> Actions </th>
                      </tr>
                    </thead>
                    <tbody id="adminsTable">
                      @foreach ($vehicles as $v)
                        @if($v->deleted_at == NULL) 
                            <tr class="clickable-row">
                                <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'>
                                    @if ($v->inspections()->count()>0)
                                        {{ date('Y-m-d',strtotime($v->inspections->last()->created_at)) }}
                                    @else
                                        N/A
                                    @endif
                                </td> 
                                <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'>
                                    @if ($v->inspections()->count()>0)
                                        @if ($v->outstandingInspections()->count()>0)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    @else
                                        No
                                    @endif
                                </td> 
                                <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'>
                                    @if ($v->inspections()->count()>0)
                                        @if ($v->outstandingInspections()->count()>0)
                                            Outstanding
                                        @else
                                            {{ $v->last_action_date }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td> 
                              <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'> 
                                {{ $v->license_plate}} 
                              </td>
                              <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'> 
                                {{ $v->vehicle_number }} 
                              </td>
                              <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'> 
                                {{ $v->mileage }} 
                              </td>
                              <td onclick='window.document.location="{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}"'> 
                                {{ $v->group->group_name }} 
                              </td>
                                  <td class="action-column"> 
                                        <!--<a href='{{ URL::to("vehicle-management/view",array($v->vehicle_id)) }}'><i class="icon-eye-open"></i></a>!-->
                                        <a href="#" id="edit_{{ $v->vehicle_id }}" class="editVehicleLink"><i class="glyphicon glyphicon-pencil"></i></a>
                                        <a href="#" id="delete_{{ $v->vehicle_id }}" class="deleteVehicleLink"><i class="glyphicon glyphicon-trash"></i></a>
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
  <script src="{{{ asset('assets/js/wkss/vehicle-management.js') }}}"></script>
@stop
@extends('webApp::layouts.withNav')
@section('styles')
    @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/worker-management/worker-management.css') }}}" rel="stylesheet">
@stop

@section('content')
<!--=============== START MODALS ===============-->
<div id="modalWorkerDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteWorkerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteWorkerLabel"> Delete Worker </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteWorkerForm')) }}
                    {{ Form::text('delete_auth_token','',array('class'=>'hide','id'=>'delete_auth_token')) }}
                    <h5> Are you sure you want to delete this worker? </h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteWorkerButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalWorkerEnable" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="enableWorkerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="enableWorkerLabel"> Enable Account </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'enableWorkerForm')) }}
                    {{ Form::text('enable_auth_token','',array('class'=>'hide','id'=>'enable_auth_token')) }}
                    <h5> Are you sure you want to re-enable this account? </h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'enableWorkerButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalWorkerAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addWorkerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="addWorkerLabel"> Add Worker </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addWorkerForm')) }}
                    <div class="control-group">
                        {{ Form::label('auth_token', 'Authentication Token', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('auth_token', Worker::generateAuthToken(), ['readonly', 'class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('email', 'Email', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('email','',array('placeholder'=>'Used for sending the token', 'class'=>'form-control', 'required'=>'true')) }}
                        </div>
                    </div> 

                    <div class="control-group">
                        {{ Form::label('first_name', 'First Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('first_name','', ['placeholder'=>'First Name', 'class'=>'form-control', 'required'=>'true']) }}
                        </div>
                    </div> 

                    <div class="control-group">
                        {{ Form::label('last_name', 'Last Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('last_name','', ['placeholder'=>'Last Name', 'class'=>'form-control', 'required'=>'true']) }}
                        </div>
                    </div>   

                    @if ($user->isAdmin())
                        <div class="control-group">
                            {{ Form::label('company_id', 'Company', array('class'=>'control-label')) }}
                            <div class="controls">
                                <select id="selectCompanyAdd" name='company_id' class='form-control'>
                                    <option value ="0" selected="selected"> Please select a company </option>
                                    @foreach ($companies as $key=>$val)
                                        <option value="{{ $key }}"> {{ $val }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> 
                    @else
                         {{ Form::text('company_id',Auth::user()->company_id,array('class'=>'hide')) }}
                    @endif
                    
                    @if ($user->isAdmin())
                    <div class="control-group">
                        {{ Form::label('division_id', 'Division', array('class'=>'control-label')) }}
                        <div class="controls">
                              <select id="selectDivisionAdd" name='division_id' class='form-control'></select>
                        </div>
                    </div>
                    @else
                        <div class="control-group">
                            {{ Form::label('division_id', 'Division', array('class'=>'control-label')) }}
                            <div class="controls">
                                 <select id="selectDivisionAdd" name='division_id' class='form-control'>
                                    <option value ="0" selected="selected"> Please select a division</option>
                                    @foreach (Auth::user()->company->divisions as $division)
                                       <option value="{{ $division->division_id }}"> {{ $division->division_name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>  
                    @endif
                    <div class="control-group">
                        {{ Form::label('business_unit_id', 'Business Unit', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select id="selectBusinessUnitAdd" name='business_unit' class='form-control'></select>
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('group_id', 'Project', array('class'=>'control-label')) }}
                        <div class="controls">
                           <select id="selectGroupAdd" name='group_id' class='form-control'></select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Create',array('class'=>'btn btn-orange medium pull-right','id'=>'addWorkerButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalWorkerDisable" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="disableWorkerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="disableWorkerLabel"> Disable Account </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'disableWorkerForm')) }}
                    {{ Form::text('disable_auth_token','',array('class'=>'hide','id'=>'disable_auth_token')) }}
                    <h5> Are you sure you want to disable this account? </h5>
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'disableWorkerButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>



<div id="modalWorkerEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editWorkerLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="editWorkerLabel"> Edit Worker </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editWorkerForm')) }}
                <div class="row">
                    <div class="col-md-5">
                        <div class="control-group">
                            {{ Form::label('edit_auth_token', 'Authentication Token', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_auth_token','',array('readonly'=>'readonly', 'class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_first_name', 'First Name', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_first_name','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_last_name', 'Last Name', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_last_name','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_home_phone', 'Home Phone', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_home_phone','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_cell_phone', 'Cell Phone', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_cell_phone','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_work_phone', 'Work Phone', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_work_phone','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_work_cell_phone', 'Work Cell Phone', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_work_cell_phone','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_birthday', 'Date of birth', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_birthday','',array('class'=>'form-control')) }}
                            </div>
                        </div>         

                    </div>
                    <div class="col-md-6">
                       <div class="control-group">
                                    {{ Form::label('division_id', 'Division', array('class'=>'control-label')) }}
                                    <div class="controls">
                                          <select id="selectDivisionEdit" name='division_id' class='form-control'></select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    {{ Form::label('business_unit_id', 'Business Unit', array('class'=>'control-label')) }}
                                    <div class="controls">
                                        <select id="selectBusinessUnitEdit" name='business_unit' class='form-control'></select>
                                    </div>
                                </div>  
                                <div class="control-group">
                                    {{ Form::label('group_id', 'Project', array('class'=>'control-label')) }}
                                    <div class="controls">
                                       <select id="selectGroupEdit" name='group_id' class='form-control'></select>
                                    </div>
                                </div> 
                        <div class="control-group">
                            {{ Form::label('edit_country', 'Country', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_country','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_state', 'State/Province', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_state','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_city', 'City', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_city','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_street', 'Street', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_street','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_suite', 'Suite', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_suite','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('edit_zip', 'ZIP/Postal code', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('edit_zip','',array('class'=>'form-control')) }}
                            </div>
                        </div>  
                    </div>
                </div>
                <div id="emergencyContacts" class="hidden">
                    <h4>
                        Emergency contacts
                    </h4>
                    <table class="table-list">
                        <thead>
                          <tr>
                            <th> Name </th>
                            <th> Contact details </th>
                            <th> Relationship </th>
                          </tr>
                        </thead>
                        <tbody id="emergencyContactsTable">
                            
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <span id='editError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editWorkerButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!--=============== END MODALS ===============-->


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
              <span class="list-component">Workers</span>
          </div>
          <span class="pull-right">
               <button id="addWorkerLink" class="btn btn-orange small">
                    Create Worker
                    <i class="glyphicon glyphicon-plus"></i>
               </button>
          </span>
        </div>
        <div class="list-container-body">
             <table class="table-list table-hover dataTable" id="workerTable">
                <thead>
                  <tr>
                    <th> Token </th>
                    <th> First Name </th>
                    <th> Last Name </th>
                    @if ($loggedUser->isAdmin())
                        <th> Company </th>
                    @else
                        <th> Division </th>
                    @endif
                    <th class="action-column"> Actions </th>
                  </tr>
                </thead>
                <tbody id="adminsTable">
                  @foreach ($workers as $a)
                        @if($a->deleted_at == NULL)
                        <tr {{ !$a->disabled?"":"class='disabled'"}} >
                          <td> {{ $a->auth_token }} </td>
                          <td> {{ $a->first_name }} </td>
                          <td> {{ $a->last_name }} </td>
                          @if ($loggedUser->isAdmin())
                                <td> {{ $a->company->company_name }} </td>
                          @else
                                 <td> {{ $a->division->division_name}} </td>
                          @endif
                              <td class="action-column"> 
                                    <a href="{{ URL::to('image/worker/profile/'.$a->auth_token) }}" data-lightbox="{{ $a->auth_token }}" title="{{ $a->auth_token }} ({{ $a->first_name }} {{ $a->last_name }})"><i class="glyphicon glyphicon-picture" title="View Worker Photo"></i></a>
                                    <a href="#" id="edit_{{ $a->auth_token }}" class="editWorkerLink"><i class="glyphicon glyphicon-pencil" title="Edit"></i></a>
                                    @if (!$a->disabled)
                                        <a href="#" id="disable_{{ $a->auth_token }}" class="disableWorkerLink"><i class="glyphicon glyphicon-ban-circle" title="Disable Account"></i></a>
                                    @else 
                                        <a href="#" id="enable_{{ $a->auth_token }}" class="enableWorkerLink"><i class="glyphicon glyhicon-check" title="Enable Account"></i></a>
                                    @endif
                                    <!--<a href='{{ URL::to("worker",array($a->auth_token)) }}'><i class="icon-eye-open"></i></a>-->
                                    <a href="#" id="delete_{{ $a->auth_token }}" class="deleteWorkerLink"><i class="glyphicon glyphicon-trash" title="Delete Account"></i></a>
                              </td>
                        </tr>
                        @endif
                  @endforeach
                </tbody>
            </table>
        </div>
    </div>               
</div>  <!-- container -->        
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/worker-management.js') }}}"></script>
@stop
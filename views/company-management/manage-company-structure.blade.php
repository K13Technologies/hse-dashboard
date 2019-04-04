@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <style>
         .list-container-body{
             height: 250px;
             overflow-y: auto;
             overflow-x: hidden;
         }
         td{
             max-width: 270px;
             overflow-x: hidden;
         }
         .modal-footer{
             text-align: center
         }
         .list-container, .span4{
             min-width: 370px;
         }
     </style>
@stop

@section('content')

<!-- START MODALS !-->
<div id="modalDivisionAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addDivisionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addDivisionLabel"> Add Division </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addDivisionForm')) }}
                    
                    {{ Form::text('company_id',$company->company_id,array('class'=>'hide')) }}
                
                    <div class="control-group">
                        {{ Form::label('division_name', 'Division Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('division_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addDivisionError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'addDivisionButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalDivisionEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editDivisionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editDivisionLabel"> Edit Division </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editDivisionForm')) }}
                
                    {{ Form::text('edit_division_id','',array('class'=>'hide','id'=>'edit_division_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_division_name', 'Division Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_division_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='editDivisionError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editDivisionButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalDivisionDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteDivisionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteDivisionLabel"> Delete Division </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteDivisionForm')) }}
                    {{ Form::text('delete_division_id','',array('class'=>'hide','id'=>'delete_division_id')) }}
                    <h5> Are you sure you want to delete this division? </h5>
            </div>
            <div class="modal-footer">
                <span id='deleteDivisionError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteDivisionButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalBusinessUnitAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addBusinessUnitLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addBusinessUnitLabel"> Add Business Unit </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addBusinessUnitForm')) }}

                    <div class="control-group">
                        {{ Form::label('business_unit_name', 'Business Unit Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('business_unit_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('selectBusinessUnitDivision', 'Division', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select class="selectBusinessUnitDivision form-control" name="division_id">
                                @foreach ($company->divisions as $division)
                                    <option value="<?php echo $division->division_id ?>"> {{ $division->division_name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addBUError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'addBusinessUnitButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalBusinessUnitEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editBusinessUnitLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editBusinessUnitLabel"> Edit Business Unit </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editBusinessUnitForm')) }}
                
                    {{ Form::text('edit_business_unit_id','',array('class'=>'hide','id'=>'edit_business_unit_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_business_unit_name', 'Business Unit Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_business_unit_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('selectBusinessUnitDivision', 'Division', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select class="selectBusinessUnitDivision form-control" name="edit_division_id">
                                @foreach ($company->divisions as $division)
                                    <option value="<?php echo $division->division_id ?>"> {{ $division->division_name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='editBUError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editBusinessUnitButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editBusinessUnitLabel"> Edit Business Unit </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editBusinessUnitForm')) }}
                
                    {{ Form::text('edit_business_unit_id','',array('class'=>'hide','id'=>'edit_business_unit_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_business_unit_name', 'Business Unit Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_business_unit_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('selectBusinessUnitDivision', 'Division', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select class="selectBusinessUnitDivision form-control" name="edit_division_id">
                                @foreach ($company->divisions as $division)
                                    <option value="<?php echo $division->division_id ?>"> {{ $division->division_name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='editBUError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editBusinessUnitButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalBusinessUnitDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteBusinessUnitLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteBusinessUnitLabel"> Delete Business Unit </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteBusinessUnitForm')) }}
                    {{ Form::text('delete_business_unit_id','',array('class'=>'hide','id'=>'delete_business_unit_id')) }}
                    <h5> Are you sure you want to delete this business unit? </h5>
            </div>
            <div class="modal-footer">
                <span id='deleteBUError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteBusinessUnitButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalGroupAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addGroupLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addGroupLabel"> Add Project </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addGroupForm')) }}
                    <div class="control-group">
                        {{ Form::label('group_name', 'Project Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('group_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                
                     <div class="control-group">
                        {{ Form::label('selectGroupBusinessUnit', 'Business Unit', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select class="selectGroupBusinessUnit form-control" name="business_unit_id">
                            </select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addGroupError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'addGroupButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalGroupEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editGroupLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editGroupLabel"> Edit Project</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editGroupForm')) }}
                
                    {{ Form::text('edit_group_id','',array('class'=>'hide','id'=>'edit_group_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_group_name', 'Project Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_group_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('selectGroupBusinessUnit', 'Business Unit', array('class'=>'control-label')) }}
                        <div class="controls">
                            <select class="selectGroupBusinessUnit form-control" name="edit_business_unit_id">
                            </select>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id="editGroupError" style="color:red"></span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editGroupButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


<div id="modalGroupDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deleteGroupLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteGroupLabel"> Delete Project </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteGroupForm')) }}
                    {{ Form::text('delete_group_id','',array('class'=>'hide','id'=>'delete_group_id')) }}
                    <h5> Are you sure you want to delete this project? </h5>
            </div>
            <div class="modal-footer">
                <span id="deleteGroupError" style="color:red"></span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteGroupButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>


<div id="modalPhoneAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addDivisionLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addDivisionLabel"> Add Phone Number </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addPhoneForm')) }}
                    {{ Form::text('company_id',$company->company_id,array('class'=>'hide')) }}
                    {{ Form::text('type',Helpline::PHONE_NUMBER,array('class'=>'hide')) }}
                    <div class="control-group">
                        {{ Form::label('title', 'Contact name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('title', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('value', 'Phone number', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('value', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addPhoneError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Add',array('class'=>'btn btn-orange medium pull-right','id'=>'addPhoneButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalRadioAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addRadioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addRadioLabel"> Add Radio Frequency </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addRadioForm')) }}
                    {{ Form::text('company_id',$company->company_id,array('class'=>'hide')) }}
                    {{ Form::text('type',Helpline::RADIO_STATION,array('class'=>'hide')) }}
                    <div class="control-group">
                        {{ Form::label('title', 'Radio station name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('title', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('value', 'Radio frequency', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('value', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <span id='addRadioError' style="color:red"> </span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Add',array('class'=>'btn btn-orange medium pull-right','id'=>'addRadioButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalPhoneDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deletePhoneLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteGroupLabel"> Delete Phone Number </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deletePhoneForm')) }}
                    {{ Form::text('helpline_id','',array('class'=>'hide','id'=>'delete_phone_id')) }}
                    <h5> Are you sure you want to delete this phone number? </h5>
            </div>
            <div class="modal-footer">
                <span id="deletePhoneError" style="color:red"></span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deletePhoneButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalRadioDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="deletePhoneLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteGroupLabel"> Delete Radio Frequency</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteRadioForm')) }}
                    {{ Form::text('helpline_id','', array('class'=>'hide','id'=>'delete_radio_id')) }}
                    <h5> Are you sure you want to delete this radio frequency? </h5>
            </div>
            <div class="modal-footer">
                <span id="deleteRadioError" style="color:red"></span>
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteRadioButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- END MODALS !-->

<div class='container'>
    <input type="hidden" id="thisCompanyId" value='{{$company->company_id}}'/>

    <div class='row'>
        <h4 class='list-component'> Organizational Structure for {{ $company->company_name }} </h4>
        <br/>
    </div>

    <div class='row'>
        
    </div>

    
    <div class="row">
        <div class="col-md-4"> 
            <div class="list-container">
                <div class="list-container-header">
                  <div class="list-component-container">
                      <span class="list-component">Divisions</span>
                  </div>
                  <span class="pull-right">
                       <button type="button" data-toggle="modal" data-target="#modalDivisionAdd" class="btn-orange small">
                            <i class="glyphicon glyphicon-plus"></i>
                       </button>
                  </span>
                </div>
                <div class="list-container-body">
                     <table class="table-list table-hover">
                        <thead>
                          <tr>
                            <th> NAME </th>
                            <th class="action-column">  </th>
                          </tr>
                        </thead>
                        <tbody id="selectDivision">
                          @foreach ($company->divisions as $division) 
                                <tr class="view-division clickable-row" id="division_{{ $division->division_id }}">
                                    <td> {{ $division->division_name }} </td>
                                    <td class="action-column"> 
                                            <a href="#" id="edit_division_{{ $division->division_id }}" class="editDivisionLink"><i class="glyphicon glyphicon-pencil"></i></a>
                                            <a href="#" id="delete_division_{{ $division->division_id }}" class="deleteDivisionLink"><i class="glyphicon glyphicon-trash"></i></a>     
                                      </td>
                                </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div> 
       <div class="col-md-4"> 
           <div class="list-container">
                <div class="list-container-header">
                  <div class="list-component-container">
                      <span class="list-component">Business Units</span>
                  </div>
                  <span class="pull-right">
                       <button type="button" data-toggle="modal" data-target="#modalBusinessUnitAdd" class="btn-orange small">
                            <i class="glyphicon glyphicon-plus"></i>
                       </button>
                  </span>
                </div>
                <div class="list-container-body">
                     <table class="table-list table-hover">
                        <thead>
                          <tr>
                            <th> NAME </th>
                            <th class="action-column">  </th>
                          </tr>
                        </thead>
                        <tbody id="selectBusinessUnit">
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="list-container">
                <div class="list-container-header">
                  <div class="list-component-container">
                      <span class="list-component">Projects</span>
                  </div>
                  <span class="pull-right">
                       <button type="button" data-toggle="modal" data-target="#modalGroupAdd" class="btn-orange small">
                            <i class="glyphicon glyphicon-plus"></i>
                       </button>
                  </span>
                </div>
                <div class="list-container-body">
                     <table class="table-list table-hover">
                        <thead>
                          <tr>
                            <th> NAME </th>
                            <th class="action-column">  </th>
                          </tr>
                        </thead>
                        <tbody id="selectGroup">
                            
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="list-container accordion" id="settings-accordion">
        <!--<a class="accordion-toggle" data-toggle="collapse" data-parent="#settings-accordion" href="#settings-body">-->
            <div class="list-container-header">
                <div class="list-component-container accordion-heading">
                    <span class="list-component"> Journey Management Settings</span>
                </div>
            </div>
        <!--</a>-->
        <!--<div class="accordion-body collapse" id="settings-body">-->
        <div id="settings-body">
            
            <div class="accordion-inner">
                
                <div class="row form-horizontal">
                    
                    <div class="col-md-6">
                        <div class="list-component"> Phone Numbers</div>
                        
                        <table class="table-list">
                            <thead>
                              <tr>
                                <th> Contact name </th>
                                <th> Phone number</th>
                                <th class="action-column"> 
                                    <button type="button" data-toggle="modal" data-target="#modalPhoneAdd" class="btn-orange small">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </th>
                              </tr>
                            </thead>
                            <tbody id="phoneList">
                                @foreach ($company->phoneNumbers as $phone)
                                    <tr id="helpline_{{$phone->helpline_id}}">
                                        <td> {{ $phone->title }} </td>
                                        <td> {{ $phone->value }} </td>
                                        <td class="action-column"> 
                                            <a href="#" id="delete_phone_{{ $phone->helpline_id }}" class="deletePhoneLink"><i class="glyphicon glyphicon-trash"></i></a>     
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                            <div class="list-component">Radio Frequencies</div>
                            
                            <table class="table-list">
                                <thead>
                                  <tr>
                                    <th> Radio station </th>
                                    <th> Frequency </th>
                                    <th class="action-column">
                                        <button type="button" data-toggle="modal" data-target="#modalRadioAdd" class="btn-orange small">
                                            <i class="glyphicon glyphicon-plus"></i>
                                        </button>
                                    </th>
                                  </tr>
                                </thead>
                                <tbody id="radioList">
                                    @foreach ($company->radioStations as $radio)
                                       <tr id="helpline_{{$radio->helpline_id}}">
                                            <td> {{ $radio->title }} </td>
                                            <td> {{ $radio->value }}    </td>
                                            <td class="action-column"> 
                                                <a href="#" id="delete_radio_{{ $radio->helpline_id }}" class="deleteRadioLink"><i class="glyphicon glyphicon-trash"></i></a>     
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    </div>
                    <br/><br/>
                    
                 </div>
                
            </div>
            
        </div>
        
    </div>
</div>
@stop

@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/wkss/company-structure-management.js') }}}"></script>
@stop
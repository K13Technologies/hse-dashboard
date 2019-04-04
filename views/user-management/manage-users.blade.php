@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')

<!-- BEGIN MODALS -->

<div id="modalAdminAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAdminAdd" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="modalEmailExport"> Add Administrator</h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'addAdminForm')) }}
                <div class="modal-body">
                        <div class="control-group">
                            {{ Form::label('email', 'Email', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('email','',array('autocomplete'=>'off', 'class'=>'form-control')) }}
                            </div>
                        </div>  

                        <div class="control-group">
                            {{ Form::label('first_name', 'First name', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('first_name','',array('autocomplete'=>'off', 'class'=>'form-control')) }}
                            </div>
                        </div>  

                        <div class="control-group">
                            {{ Form::label('last_name', 'Last name', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::text('last_name','',array('autocomplete'=>'off', 'class'=>'form-control')) }}
                            </div>
                        </div>  

                         @if (!$user->isAdmin())
                            <div class="control-group">
                                {{ Form::label('role_id', 'Role', array('class'=>'control-label')) }}
                                <div class="controls">
                                    {{ Form::select('role_id', $canAdd, NULL, array('class'=>'form-control')) }}
                                </div>
                            </div>  
                        @else
                            <div class="control-group">
                                {{ Form::label('company_id', 'Company', array('class'=>'control-label')) }}
                                <div class="controls">
                                    {{ Form::select('company_id', $companies, NULL, array('class'=>'form-control')) }}
                                </div>
                            </div>  
                        @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                    {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'addAdminButton')) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>


<div id="modalAdminDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalAdminDelete" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="deleteAdminLabel"> Delete Administrator </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteAdminForm')) }}
                <div class="modal-body">
                    {{ Form::text('delete_admin_id','',array('class'=>'hide','id'=>'delete_admin_id')) }}
                    <h5> Are you sure you want to delete this user? </h5>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                    {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteAdminButton')) }} 
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>


<div id="modalAdminEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editAdminLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="editAdminLabel"> Edit Administrator </h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'editAdminForm')) }}
                <div class="modal-body">
                    {{ Form::text('edit_admin_id','',array('class'=>'hide','id'=>'edit_admin_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_email', 'Email', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_email', '', array('class'=>'form-control')) }}
                        </div>
                    </div>  

                    <div class="control-group">
                        {{ Form::label('edit_first_name', 'First name') }}
                        <div class="controls">
                            {{ Form::text('edit_first_name', '' , array('class'=>'form-control')) }}
                        </div>
                    </div>  
                    
                    <div class="control-group">
                        {{ Form::label('edit_last_name', 'Last name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_last_name', '', array('class'=>'form-control')) }}
                        </div>
                    </div>  
                    
                     @if (!$user->isAdmin())
                        <div class="control-group">
                            {{ Form::label('edit_role_id', 'Role', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::select('edit_role_id', $canEdit, NULL, array('class'=>'form-control')) }}
                            </div>
                        </div>  
                    @else
                        <div class="control-group">
                            {{ Form::label('edit_company_id', 'Company', array('class'=>'control-label')) }}
                            <div class="controls">
                                {{ Form::select('edit_company_id', $companies, NULL, array('class'=>'form-control')) }}
                            </div>
                        </div>  
                    @endif
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                    {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editAdminButton')) }}
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>    
<!-- END MODALS -->

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
                      <span class="list-component">All Admins</span>
                  </div>
                  <span class="pull-right">
                       @if ($canAdd)
                            <button data-toggle="modal" href="#modalAdminAdd" class="btn-orange small">
                                Create Admin <i class="glyphicon glyphicon-plus-sign"></i>
                            </button>
                        @endif
                  </span>
                </div>
                <div class="list-container-body">
                     <table class="table-list table-hover dataTable" id="userTable">
                            <thead>
                              <tr>
                                <th> Email </th>
                                <th> First Name </th>
                                <th> Last Name </th>
                                <th> Phone Number</th>
                                <th> Role </th>
                                @if ($user->isAdmin())
                                    <th> Company </th>
                                @endif
                                @if ($canEdit) 
                                    <th class="action-column"> Actions </th>
                                @endif
                              </tr>
                            </thead>
                            <tbody id="adminsTable">
                              @foreach ($admins as $a) 
                                @if ($a->admin_id != Auth::user()->admin_id )
                                    <tr>
                                @else
                                    <tr style="background-color: #f0f0f0;border-left:5px solid #8d8df0">
                                @endif
                                        <td> {{ $a->email }} </td>
                                        <td> {{ $a->first_name }} </td>
                                        <td> {{ $a->last_name }} </td>
                                        <td> {{ $a->phone_number }} </td>
                                        <td> {{ $a->role->role_name }} </td>
                                        @if ($user->isAdmin())
                                            <td> {{ $a->company->company_name }} </td>
                                        @endif
                                        @if ($canEdit) 
                                            <td class="action-column"> 
                                                      <a href="#" id="edit_{{ $a->admin_id }}" class="editAdminLink"><i class="glyphicon glyphicon-pencil"></i></a>
                                                      <a href="#" id="delete_{{ $a->admin_id }}" class="deleteAdminLink"><i class="glyphicon glyphicon-trash"></i></a>
                            <!--                          <li><a href="#"><i class="icon-ban-circle"></i> Deactivate </a></li>
                                                      <li><a href="#"><i class="icon-check"></i> Activate </a></li>-->
                                                      <!--<li><a href='{{ URL::to("cloak",array($a->admin_id)) }}'><i class="icon-lock"></i> Login as this user </a></li>-->
                                            </td>
                                        @endif
                                    </tr>
                              @endforeach
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/wkss/admin-management.js') }}}"></script>
@stop
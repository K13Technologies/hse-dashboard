@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <style>
         .table-list td:nth-child(1){
             width:50%;
         }
     </style>
@stop

@section('content')

<!--=============== START MODALS ===============-->
<div id="modalEnterpriseCompanyAdd" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addCompanyLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="addCompanyLabel"> Add Enterprise Company </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'addEnterpriseCompanyForm')) }}
                    <div class="control-group">
                        {{ Form::label('company_name', 'Company Name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('company_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
                Add a company that uses the Enterprise billing plan. Adding an enterprise company will automatically set their end of subscription date to 1 year from now.
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'addEnterpriseCompanyButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalCompanyEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editCompanyLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editCompanyLabel"> Edit company </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editCompanyForm')) }}
                    {{ Form::text('edit_company_id','',array('class'=>'hide','id'=>'edit_company_id')) }}
                    <div class="control-group">
                        {{ Form::label('edit_company_name', 'Company name', array('class'=>'control-label')) }}
                        <div class="controls">
                            {{ Form::text('edit_company_name', '', ['class'=>'form-control']) }}
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editCompanyButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="modalCompanyTimeEdit" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editCompanyTimeLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="editCompanyLabel"> Edit the date when the company's subscription ends</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'editCompanyTimeForm')) }}
                
                    {{ Form::text('edit_company_id','',array('class'=>'hide','id'=>'time_company_id')) }}
                
                    <div class="control-group">
                        {{ Form::label('edit_subscription_ends_at', 'Subscription will end on', array('class'=>'control-label')) }}
                        <div class="controls">
                            <input type='text' id="edit_subscription_ends_at" class='form-control' name='edit_subscription_ends_at' placeholder='yyyy-mm-dd' value=''/>
                        </div>
                    </div>  
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Close</button>
                {{ Form::button('Save',array('class'=>'btn btn-orange medium pull-right','id'=>'editCompanyTimeButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<!-- <div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="deleteCompanyLabel"> Delete company </h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'deleteCompanyForm')) }}
                    {{ Form::text('delete_company_id','',array('class'=>'hide','id'=>'delete_company_id')) }}
                    <h5> Are you sure you want to delete this company? </h5>
            </div>
            <div class="modal-footer" >
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">No</button>
                <div style="color:red;text-align: center;display: inline-block;max-width: 390px;" id="deleteError" class="pull-left"> </div>
                {{ Form::button('Yes',array('class'=>'btn btn-orange medium pull-right','id'=>'deleteCompanyButton')) }}
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div> -->
<!--=============== END MODALS ===============-->


<!--=============== START VISIBLE CONTENT ===============-->
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
              <span class="list-component">All Companies</span>
          </div>
          <span class="pull-right">
                 <button class="btn btn-primary btn-md" id="addEnterpriseCompany">
                    Add Enterprise Company &nbsp; <i class="glyphicon glyphicon-plus"></i>
                </button>
          </span>
        </div>
        <div class="list-container-body">
            <table class="table-list table-hover dataTable" id="companyManagementTable">
                <thead>
                    <tr>
                        <th> Company Name </th>
                        <th> Subscription Type </th>
                        <th> Subscription Status </th>
                        <th> End Of Service Date </th>
                        <th> Stripe Plan </th>
                        <th class="action-column"> Actions </th>
                    </tr>
                </thead>
                <tbody id="adminsTable">
                    @foreach ($companies as $company) 
                        <tr class="clickable-row">
                            <td onclick='window.document.location="{{ URL::to("company-management",array($company->company_id)) }}"'> 
                                {{ $company->company_name }} 
                            </td>
                            <td onclick='window.document.location="{{ URL::to("company-management",array($company->company_id)) }}"'> 
                                {{ ($company->is_enterprise)?"Enterprise":"Regular" }} 
                            </td>
                            <td onclick='window.document.location="{{ URL::to("company-management",array($company->company_id)) }}"'> 
                                <?php $timeEdit = false; $field="";?>
                                @if($company->onTrial())
                                    Trialing ({{$company->trialDaysLeft()}} days left)
                                    <?php $timeEdit = true; $field='trial_ends_at';?>
                                @elseif($company->onGracePeriod())
                                    @if($company->is_enterprise)
                                        Enterprise subscription ({{$company->gracePeriodDaysLeft()}} days left)
                                        <?php $timeEdit = true; $field='subscription_ends_at';?>
                                    @else
                                        On grace period ({{$company->gracePeriodDaysLeft()}} days left)
                                    @endif
                                @elseif($company->everSubscribed() && $company->stripeIsActive())
                                    Paid subscription 
                                @elseif($company->everSubscribed() && !$company->stripeIsActive())
                                    Subscription cancelled
                                @else
                                    @if($company->is_enterprise)
                                        Enterprise subscription
                                        <?php $timeEdit = true; $field='subscription_ends_at';?>
                                    @else
                                        Trial ended &amp; never subscribed.   
                                    @endif
                                     
                                @endif    
                            </td>
                            <td onclick='window.document.location="{{ URL::to("company-management",array($company->company_id)) }}"'>
                                @if($timeEdit)
                                    {{ substr($company->$field,0,10) }}
                                @else   
                                    N/A
                                @endif
                            </td>
                            <td>
                                {{ $company->stripe_plan ? $company->stripe_plan : "None" }}
                            </td>
                            <td class="action-column"> 
                                @if($timeEdit)
                                    <a href="#" id="edit_time_{{ $company->company_id }}" class="editCompanyEndTime" title="Change end of subscription date"><i class="glyphicon glyphicon-calendar"></i></a> |
                                @endif
                                <a href="#" id="edit_{{ $company->company_id }}" class="editCompanyLink"><i class="glyphicon glyphicon-pencil"></i></a>
                                <!--<a href="#" id="delete_{{ $company->company_id }}" class="deleteCompanyLink"><i class="icon-trash"></i></a> !-->    
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div> <!-- list-container-body -->
    </div> <!-- list-container -->
</div> <!-- container -->
@stop

@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/company-management.js') }}}"></script>
@stop
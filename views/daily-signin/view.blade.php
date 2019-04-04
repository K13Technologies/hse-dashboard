@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')
<div class='container'>
   <div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">Daily Sign In</span>
              </div>
            </div>
            <div class="list-container-body">
                    <div class="modal-body">
                    {{ Form::open(array('class'=>'form-horizontal row-fluid','id'=>'selectDailySignin')) }}
                    <div class='span6 offset3'>
                        @if ($user->isAdmin())
                            <div class="control-group">
                                {{ Form::label('company_id', 'Company', array('class'=>'control-label')) }}
                                <div class="controls">
                                    <select id="selectCompanyAdd" name='company_id' class='form-control'>
                                        @if(!$company instanceOf Company)
                                            <option value="-1" selected='selected'> Please select a company </option>
                                        @endif
                                        @foreach ($companies as $key=>$val)
                                            @if($company instanceOf Company && $company->company_id == $key)
                                                <option value="{{ $key }}" selected="selected"> {{ $val }} </option>
                                            @else
                                                <option value="{{ $key }}"> {{ $val }} </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div> 
                        @else
                             {{ Form::text('company_id',Auth::user()->company_id,array('class'=>'hide')) }}
                        @endif
                        
                        <div class="control-group">
                            {{ Form::label('group_id', 'Project', array('class'=>'control-label')) }}
                            <div class="controls">
                               <select id="selectGroupAdd" name='group_id' class='form-control'>
                                    @if(!$group instanceOf Group)
                                         <option value="-1" selected='selected'> Please select a project </option>
                                    @endif
                                    @foreach ($groupsForCompany as $g)
                                            @if($group instanceOf Group && $group->group_id == $g->group_id)
                                                <option value="{{ $g->group_id }}" selected='selected'> {{ $g->group_name }} </option>
                                            @else
                                                <option value="{{ $g->group_id }}"> {{ $g->group_name }} </option>
                                            @endif
                                    @endforeach
                               </select>
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('range_from', 'Date', array('class'=>'control-label')) }}
                            <div class="controls">
                                <input type='text' value="{{ $signDate }}" name='sign_date' id="sign_date" class="range-date-picker form-control" placeholder='yyyy-mm-dd'/>
                            </div>
                        </div> 
                        <br/> 
                        <div class="control-group">
                            <div class="controls">
                                <button class="btn btn-orange small showSignins">
                                    Show results
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </button>
                            </div>
                        </div>  
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>        
    
@if ($signins) 

<!-- START MODALS !-->
<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="modalEmailExport"> Email this Daily Sign In Report</h4>
          </div>
          <div class="modal-body">
              {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <h5> Please enter an email address (multiple separated by commas):</h5>
                {{ Form::text('signDate',$signDate,array('class'=>'hide','id'=>'email_signDate')) }}
                {{ Form::text('groupId',$group->group_id,array('class'=>'hide','id'=>'email_groupId')) }}
                {{ Form::textarea('email','',array('placeholder'=>'email@address.com','rows'=>'3','class'=>'form-control','id'=>'email')) }}
          </div>
          <div class="modal-footer">
              <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Cancel</button>
              {{ Form::button('Send',array('class'=>'btn btn-orange medium pull-right','id'=>'completeExportToMail')) }}
              <span id='mailError' style="color:red"> </span>
              {{ Form::close() }}
          </div>
        </div>
    </div>
</div>
<!-- END MODALS !-->
      

    <div class="list-container">
           <div class="list-container-header">
             <div class="list-component-container">
                 <span class="list-component"> Report for {{ $signDate }} </span>
             </div>
                   <span class="pull-right export-button">
                       <a href='{{ URL::to("daily-signin/export",array($group->group_id,$signDate)) }}'>
                              <button class="btn btn-orange small">
                                  Export to PDF
                                  <i class="glyphicon glyphicon-file"></i>
                              </button>
                        </a>
                   </span>
                   <span class="pull-right export-button">
                       <a href="#modalEmailSave" data-toggle="modal">
                           <button class="btn btn-orange small">
                               Send via Email
                               <i class="glyphicon glyphicon-envelope"></i>
                           </button>
                       </a>
                   </span>
           </div>
           <div class="list-container-body">
                <table class="table-list">
                    <thead>
                      <tr>
                        <th> Name </th>
                        <th> Sign-in times </th>
                        <th> Sign-out times </th>
                        <th> Sign-in signature </th>
                        <th> Sign-out signature </th>
                      </tr>
                    </thead>
                    <tbody id="dailySigninsTable">
                         @foreach ($signins as $s)
                            <tr>
                                <td>
                                    {{ $s['name'] }}
                                </td>
                                <td>
                                    @if (!empty($s['signins']))
                                        @foreach ($s['signins'] as $in)
                                            {{ $in->created_at }} <br/>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                     @if (!empty($s['signouts']))
                                        @foreach ($s['signouts'] as $out)
                                            {{ $out->created_at }} <br/>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                  @if (!empty($s['signins']))
                                    <?php $in = last($s['signins']); ?>
                                    <img src='{{ URL::to("image/daily-signin/{$in->daily_signin_id}/signature")}}' class="signature-image"/>
                                  @else
                                      N/A
                                  @endif
                                </td>
                                <td>
                                  @if (!empty($s['signouts']))
                                    <?php $out = last($s['signouts']); ?>
                                    <img src='{{ URL::to("image/daily-signin/{$out->daily_signin_id}/signature")}}' class="signature-image"/>
                                  @else
                                      N/A
                                  @endif
                                </td>
                            </tr>
                        @endforeach  
                    </tbody>
                </table>
           </div>
       </div>
</div>     
@endif
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/daily-signin.js') }}}"></script>
@stop
@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/company-stats.css') }}}" rel="stylesheet">
@stop

@section('content')


<div class='container'>
    <div class='row well whiteBackground'>
        <div class='col-md-9'>
            <div class="section-header-label">Statistics</div>

            <div class='row'>
                <div class='col-md-8'>
                    {{ Form::open(array('class'=>'form-horizontal row-fluid','id'=>'selectStats')) }}
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
                            {{ Form::label('ref_timeframe', 'Timeframe', array('class'=>'control-label')) }}
                            <div class="controls">
                                <select id="selectTimeframe" name='timeframe' class='form-control'>
                                        <option value="" {{ ($timeframe=="")?"selected='selected'":""}} > Please select a timeframe</option>
                                        <option value="weekly" {{ ($timeframe=="weekly")?"selected='selected'":""}}>A week before</option>
                                        <option value="monthly" {{ ($timeframe=="monthly")?"selected='selected'":""}}>A month before</option>
                                        <option value="yearly" {{ ($timeframe=="yearly")?"selected='selected'":""}}>A year before</option>
                                        <option value="forever" {{ ($timeframe=="forever")?"selected='selected'":""}}>The beginning of time</option>
                                </select>
                            </div>
                        </div>  

                        <div class="control-group">
                            {{ Form::label('ref_date', 'Date', array('class'=>'control-label')) }}
                            <div class="controls">
                                <input type='text' value="{{ $refDate }}" name='ref_date' id="ref_date" class="range-date-picker form-control" placeholder='yyyy-mm-dd'/>
                            </div>
                        </div>  
                        <br/>
                        <div class="control-group">
                            <div class="controls">
                                <button class="btn btn-orange small showSignins">
                                    Show results
                                    <i class="glyphicon glyphicon-calendar"></i>
                                </button>
                                <br/><br/>
                            </div>
                        </div>  
                    {{ Form::close() }}
                </div>
            </div> <!-- row -->
        </div>
        <div class='col-md-3'>
            @if($exportButtons)
                <span class="export-button">
                     <a href='{{ URL::to("company-management/stats/export",array($group->group_id, $refDate, $timeframe)) }}'>   
                            <button class="btn btn-orange small">
                                Export to PDF
                                <i class="glyphicon glyphicon-file"></i>
                            </button>
                      </a>
                </span>
                <span class="export-button">
                    <a href="#modalEmailSave" data-toggle="modal">
                        <button class="btn btn-orange small">
                            Send via email
                            <i class="glyphicon glyphicon-envelope"></i>
                        </button>
                    </a>
                </span>
            @endif
        </div>
    </div>  <!-- well -->      
    

@if ($stats) 

<!-- START MODALS !-->
<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="modalEmailExport"> Email this Statistics Report</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                    <h5> Please enter an email address (multiple separated by commas):</h5>

                    {{ Form::text('refDate',$refDate,array('class'=>'hide','id'=>'email_refDate')) }}
                    {{ Form::text('groupId',$group->group_id,array('class'=>'hide','id'=>'email_groupId')) }}
                    {{ Form::text('timeframe',$timeframe,array('class'=>'hide','id'=>'email_timeframe')) }}
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


    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Near Misses</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['nearMiss']['total'] }} </div>
                With corrective actions not implemented on site <br/>
                <div class="minor-number" >{{ $stats['nearMiss']['areq'] }} </div>
                Of which, with corrective actions implemented by admins<br/>
                <div class="of-which" >{{ $stats['nearMiss']['afix'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Hazards</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['hazard']['total'] }} </div>
                With corrective actions not implemented on site <br/>
                <div class="minor-number" >{{ $stats['hazard']['areq'] }} </div>
                Of which, with corrective actions implemented by admins<br/>
                <div class="of-which" >{{ $stats['hazard']['afix'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Field Observations</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['po']['total'] }} </div>
                With corrective actions not implemented on site <br/>
                <div class="minor-number" >{{ $stats['po']['areq'] }} </div>
                Of which, with corrective actions implemented by admins<br/>
                <div class="of-which" >{{ $stats['po']['afix'] }} </div>
            </div>
        </div>
    </div>


    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Incidents</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['incident']['total'] }} </div>
                With corrective actions not implemented on site <br/>
                <div class="minor-number" >{{ $stats['incident']['areq'] }} </div>
                Of which, with corrective actions implemented by admins<br/>
                <div class="of-which" >{{ $stats['incident']['afix'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Daily Sign Ins</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total<br/>
                <div class="major-number" >{{ $stats['signin']['total'] }} </div>
                Total Sign Ins  <br/>
                <div class="minor-number" >{{ $stats['signin']['in'] }} </div>
                Total Sign Outs<br/>
                <div class="minor-number" >{{ $stats['signin']['out'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">FLHAs</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['flha']['total'] }} </div>
                Without Job Completions <br/>
                <div class="minor-number" >{{ $stats['flha']['areq'] }} </div>
                With Job Competions<br/>
                <div class="minor-number" >{{ $stats['flha']['afix'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Tailgates</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['tailgate']['total'] }} </div>
                Without Job Completions <br/>
                <div class="minor-number" >{{ $stats['tailgate']['areq'] }} </div>
                With Job Competions<br/>
                <div class="minor-number" >{{ $stats['tailgate']['afix'] }} </div>
            </div>
        </div>
    </div>

    <div class="row well whiteBackground">
        <div class='col-md-12'>
            <div class="section-header-label">Inspections</div>
            <br/>
        </div>
        <div class='col-md-12'>
            <div class="center">
                Total <br/>
                <div class="major-number" >{{ $stats['inspection']['total'] }} </div>
                With action required<br/>
                <div class="minor-number" >{{ $stats['inspection']['areq'] }} </div>
                OK or all actions completed<br/>
                <div class="minor-number" >{{ $stats['inspection']['afix'] }} </div>
            </div>
        </div>
    </div>
@endif
</div> <!-- container -->
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/wkss/company-stats.js') }}}"></script>
@stop
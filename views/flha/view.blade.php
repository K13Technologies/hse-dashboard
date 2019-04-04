@extends('webApp::layouts.withNav')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">
    <style>
        .row-fluid{
            margin-left:-10px!important;
        }
        .span3:first-child{
            padding-left:10px;
        }
        .high-risk{
            color: #b94a48;
        }
        .medium-risk{
            color:#c09853;
        }
        .low-risk{
            color:#468847;
        }
        .modal-photo{
            max-width: 75px;
            max-height: 75px;
            display:inline-block;
        }
        #modalWorker{
            width: 960px;
            margin-left: -480px;
        }
        #modalSpotcheck{
            width: 960px;
            margin-left: -480px;
        }
        .deleteItemBtn { 
            cursor: pointer; 
        }
        .list-container {
            padding-bottom:20px;
        }
        .hazardWell {
            padding: 3px;
        }
        .task-numbering {
            background-color:grey; 
            color:white;
            font-weight:bold;
            margin-right:7px;
            padding:5px;
            margin-top:7px;
            font-size:18px;
            width:55px;
            text-align: center;
        }
        .hazard-numbering {
            background-color:grey;
            color:white;
            font-weight:bold;
            margin-right:7px;
            padding:5px;
            margin-top:7px;
            margin-bottom:7px;
            font-size:15px;
            width:80px;
            text-align: center;
        }
        #selectHelpButton { 
            cursor: pointer; 
        }
        #exportPDFButton {
            margin-left: -9px;
        }
        .riskHelpBtn { 
            cursor: pointer; 
        }
        #signaturePic {
            width: 150px;
            height: 75px;
        }
        .photo-slider img {
            width:100%;
        }

    </style>
@stop

@section('content')
<!-- START MODALS !-->
<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="modalEmailExport"> Email this FLHA Card</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                
                    <h5> Please enter an email address (multiple separated by commas):</h5>
                    {{ Form::text('flha_id',$flha->flha_id,array('class'=>'hide')) }}
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

    <!-- UNUSED MODALS .... NEED TO BE UPDATED -->
    <div id="modalVisitor" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addDivisionLabel" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="addDivisionLabel"> View Visitor</h4>
        </div>
        <div class="modal-body form-horizontal">
            <div class="control-group">
                {{ Form::label('visitor_name', 'Visitor Name', array('class'=>'control-label')) }}
                <div class="controls">
                    <div class="modal-content-description" id='modalVisitorName'></div>
                </div>
            </div>  
            <div class="control-group">
                {{ Form::label('profile_photo', 'Signature', array('class'=>'control-label')) }}
                <div class="controls">
                    <a data-lightbox="visitor-signature-photo">
                        <img id="signatureVisitor" class="modal-photo"/>
                    </a>
                </div>
            </div>  
        </div>
        <div class="modal-footer">
            <button class="btn-grey" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>

    <div id="modalWorker" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="viewWorkerDetails" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="viewWorkerDetails"> View Worker</h4>
        </div>
        <div class="modal-body form-horizontal">
             <div class="row-fluid">
                 <div class="span5">
                        <div class="control-group">
                            {{ Form::label('worker_name', 'Worker Name', array('class'=>'control-label')) }}
                            <div class="controls">
                                <div class="modal-content-description" id='modalWorkerName'></div>
                            </div>
                        </div>  
                        <div class="control-group">
                            {{ Form::label('profile_photo', 'Signature', array('class'=>'control-label')) }}
                            <div class="controls">
                                <a data-lightbox="worker-signature-photo">
                                    <img id="signatureWorker" class="modal-photo"/>
                                </a>
                            </div>
                        </div>  
                    </div>
                 <div class="span6">
                    <div class="control-group">
                        {{ Form::label('breaks', 'Worker\'s Breaks', array('class'=>'control-label')) }}
                        <div class="controls">
                            <div id="workerBreaks">
                                
                            </div>
                        </div>
                    </div>  
                     
                 </div>
             </div> 
        </div>
        <div class="modal-footer">
            <button class="btn-grey" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
    <!-- END UNUSED MODALS -->


<!-- END MODALS !-->


<!-- ****************************************************** START VISIBLE CONTENT !-->
<div class='container'>
@if(isset($flha))
{{ Form::model($flha, array('id'=>'editFlhaForm')) }}  

    <div class='row well whiteBackground'>
        <div class='col-md-8'>
            <span class="list-component">FLHA Title: <input class='form-control' type='text' data-bind='value: title' name='title' required maxlength='100' id='flhaTitle'/></span>
        </div>
        <div class='col-md-4'>
            <span>
                <input href="#" type="submit" value="Save Edits" class="editFlhaButton btn btn-success"/>
            </span>
            <span class="export-button">
                <a href="#modalEmailSave" data-toggle="modal">
                    <button class="btn btn-orange small" type='button'>
                        Send via email
                        <i class="glyphicon glyphicon-envelope"></i>
                    </button>
                </a>
            </span>
            <span class="export-button">  
                <a href='{{ URL::to("flha/export",array($flha->flha_id)) }}'> 
                    <button class="btn btn-orange small" id="exportPDFButton" type='button'>
                        Export to PDF
                        <i class="glyphicon glyphicon-file"></i>
                    </button>
                </a>
            </span>  
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class="col-md-3">
            <div class='row'>
                <div class="section-header-label"> Creation Date</div>
                <span title="Created on server at: {{ $flha->created_at }}" class="content-description"> 
                    {{ WKSSDate::display($flha->ts,$flha->created_at) }} 
                </span>  
            </div>    
            <div class="row"> 
                <div class="section-header-label">Project Management</div>
                </br></br></br></br></br>
            </div>
        </div>
        <div class='col-md-1'></div>
        <div class='col-md-8'>
            <div class='row'>
                <div class="section-header-label"> Job Description </div>
                </br>
                <textarea class='form-control' name='job_description' data-bind='value:job_description' rows=4 required></textarea>
            </div>

            <div class='row'>        
                <div class='col-md-12'>
                    <div class="section-header-label"> FLHA Details </div>
                </div>
                <div class='col-md-4'>
                    {{ Form::label('muster_point', 'Muster Point', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control' data-bind='value: muster_point' maxlength='100' required/> </div>
                </div> 
                <div class='col-md-4'>
                    {{ Form::label('client', 'Client', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control' data-bind='value: client' maxlength='100' required/>  </div>
                </div>
                <div class='col-md-4'>
                    {{ Form::label('radio_channel', 'Radio Channel', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control'  data-bind='value: radio_channel' maxlength='100'/>  </div>
                </div>

                <div class='col-md-4'>
                    {{ Form::label('supervisor_name', 'Supervisor Name', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control' data-bind='value: supervisor_name' maxlength='100' required/>  </div>
                </div>
                <div class='col-md-4'>
                    {{ Form::label('supervisor_number', 'Supervisor Number', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control' data-bind='value: supervisor_number' maxlength='100' placeholder='###-###-####' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####' required/>  </div>
                </div>
                <div class='col-md-4'>
                    {{ Form::label('safety_rep_name', 'Safety Rep Name', ['class' => 'content-label']) }}
                    <div class="content-description"> <input type='text' class='form-control' data-bind='value: safety_rep_name' maxlength='100'/>  </div>
                </div>
            </div>


            <div class='row'>
                <div class='col-md-12'>
                    <div class='col-md-12'>
                        <p class="section-header-label"> Locations </p>
                    </div>
                    <div class='col-md-12'>
                        <button type="button" class="btn btn-sm btn-primary" data-bind='click: addLocation'>Add Location</button>
                        <br/><br/>
                    </div>
                    <div class='col-md-12' data-bind='foreach: locations'>  
                        <div class='col-md-10'>
                            <input type="text" class='form-control' data-bind="attr: {'name':'locations['+ $index()+']'}, value: location" required placeholder="Location" maxlength="100"></input>
                        </div>
                        <div class='col-md-2'>
                            <i class="glyphicon glyphicon-trash deleteItemBtn" data-bind='click: $root.removeLocation'></i>
                            <br/><br/>
                        </div>
                    </div>
                </div>
            </div>

            <div class='row'>
                <div class='col-md-12'>
                    <div class='col-md-12'>
                        <p class="section-header-label"> LSDs </p>
                    </div>
                    <div class='col-md-12'>
                        <button type="button" class="btn btn-sm btn-primary" data-bind='click: addLSD'>Add LSD</button>
                        <br/><br/>
                    </div>
                    <div class='col-md-12' data-bind='foreach: lsds'>  
                        <div class='col-md-10'>
                            <input class='form-control' type="text" data-bind="attr: {'name':'lsds['+ $index()+']'}, value: lsd" title="###/##-##-###-##-W#M" pattern="\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM" placeholder="###/##-##-###-##-W#M" maxlength="30"></input>
                        </div>
                        <div class='col-md-2'>
                            <i class="glyphicon glyphicon-trash deleteItemBtn" data-bind='click: $root.removeLSD'></i>
                            <br/><br/>
                        </div>
                    </div>
                </div>
            </div>

            <div class='row'>
                <div class='col-md-12'>
                    <div class='col-md-12'>
                        <p class="section-header-label"> Sites </p>
                    </div>
                    <div class='col-md-12'>
                        <button type="button" class="btn btn-sm btn-primary" data-bind='click: addSite'>Add Site</button>
                        <br/><br/>
                    </div>
                    <div class='col-md-12' data-bind='foreach: sites'>  
                        <div class='col-md-10'>
                            <input type="text" class='form-control' data-bind="attr: {'name':'sites['+ $index()+']'}, value: site" maxlength="100"></input>
                        </div>
                        <div class='col-md-2'>
                            <i class="glyphicon glyphicon-trash deleteItemBtn" data-bind='click: $root.removeSite'></i>
                            <br/><br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class='row well whiteBackground'>
        <div class="section-header-label col-md-12"> Safety Questions </div>
        <div class='col-md-4'>
            <div class="content-label">Do you need to remove your gloves ?</div>
            <div class="content-description"> 
                @if ($flha->gloves_removed)
                    <ul><li>Yes</li></ul>
                    <textarea class='form-control' data-bind='value: gloves_removed_description' placeholder="Describe why the gloves had to be removed" rows=4 required></textarea>
                @else
                    <ul><li>No</li></ul>
                @endif
            </div>
        </div>

        <div class='col-md-4'>
            <div class="content-label">Are you working alone?</div>
            <div class="content-description"> 
                @if ($flha->working_alone)
                    <ul><li>Yes</li></ul>
                    <textarea class='form-control' data-bind='value: working_alone_description' placeholder="Describe why you had to work alone" rows=4 required></textarea>
                @else
                    <ul><li>No</li></ul>
                @endif
            </div>
        </div>
        <div class='col-md-4'>
            <div class="content-label">Warning ribbon needed?</div>
            <div class="content-description"> 
                @if ($flha->warning_ribbon)
                    <ul><li>Yes</li></ul>
                    <textarea class='form-control' data-bind='value: warning_ribbon_description' placeholder="Describe why the warning ribbon is required" rows=4 required></textarea>
                @else
                    <ul><li>No</li></ul>
                @endif
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-12'><div class="section-header-label"> Hazard Checklist </div></div>

        @foreach ($flha->checklist as $categoryId => $hazardList)
            <div class='col-md-2'>
                <div class="content-label"> {{ HazardChecklistCategory::find($categoryId)->category_name }} </div>
                <ul class="content-description">
                    @foreach ( $hazardList as $h )
                        <li> {{ $h->item_name }} </li>
                    @endforeach
                </ul>
            </div>
        @endforeach 
    </div>


    <div class='row well whiteBackground'>
        <div id="tasksContainer" class="col-md-12">
            <div class="section-header-label"> Tasks Observed</div>
            </br>
            <p><button type="button" class="btn btn-sm btn-primary" data-bind='click: $root.addTask'>Add Task</button></p>

            <div data-bind='foreach: tasks' class='col-md-12'>
                <div class="well taskContainer col-md-4">
                    <button type="button" class="btn btn-xs btn-warning pull-right" data-bind='visible: $root.tasks().length > 1, click: $parent.removeTask'>Delete Task</button>
                    <p class="task-numbering pull-left" data-bind="text:'Task ' + ($index() + 1)"> </p>
                    <input type='hidden' data-bind='value: flha_task_id, attr: {"name":"tasks[" + $index() + "][flha_task_id]"}'/>

                    <p class="content-label"> Task Title</p>
                    <input class='form-control' type="text" data-bind="attr: {'name':'tasks['+ $index()+'][title]'}, value: title" required placeholder="Task Title" maxlength="100"></input>
                    <button type="button" class="btn btn-primary btn-xs viewHazardBtn" data-bind="click: changeHazardVisibility">Show / Hide Hazards</button>
                    </br>
                    <hr/>

                    <div data-bind="visible: hazardsAreVisible">
                        <div data-bind="foreach: hazards">
                            <div class="hazardContainer"> 
                                <button type="button" class="btn btn-xs btn-warning deleteHazardBtn pull-right" data-bind='click: $parent.removeHazard'>Delete Hazard</button>  
                                <div class='hazard-numbering' data-bind="text:'Hazard ' + ($parentContext.$index() + 1) + '.' + ($index() + 1), style: { backgroundColor: hazardRiskLevelColour}"></div> 
                                <div class="">
                                    <div class="content-label"> Hazard Details</div>
                                    <textarea class='form-control' data-bind='value: description, attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][description]"}' placeholder="Describe the hazard" rows=4></textarea>
                                    <div class="content-label"> Risk Level</div>
                                    <select class='form-control' data-bind='options: riskLevels, optionsText: "label", optionsValue: "riskValue", value: risk_level, attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][risk_level]"}'></select>
                                    <!--<button type='button' class='riskHelpBtn'><i class="icon-black icon-question-sign"></i></button>!-->
                                </div>
                                <div class="">
                                    <div class="content-label"> Eliminate/Control Hazard</div>
                                    <textarea class='form-control' data-bind='value: eliminate_hazard,attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][eliminate_hazard]"}' placeholder="What was done to eliminate or control the hazard? Is there action required?" rows=4></textarea>

                                    <div class="content-label"> Risk Level</div>
                                    <select class='form-control' data-bind='options: riskLevels, optionsText: "label", optionsValue: "riskValue", value: risk_assessment, attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][risk_assessment]"}'></select>
                                    <input type='hidden' data-bind='value: flha_task_hazard_id, attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][flha_task_hazard_id]"}'/>
                                    
                                </div>
                                <hr/>
                            </div>
                        </div>
                        </br>
                        <button type="button" class="btn btn-sm btn-primary" data-bind='click: addHazard'>Add Hazard</button>
                    </div>
                </div>
            </div>    
        </div>
    </div>

    <div class="row well whiteBackground"> 
        <div class='col-md-6'>
            <div class="section-header-label"> Workers </div>
            <div class="content-description"> 
                <?php $count = 0; ?>
                @foreach ( $flha->signoffWorkers as $w )
                    <div class=''> {{ $w->first_name.' '.$w->last_name }} <img id='signaturePic' src='{{ URL::to("image/flha/worker/$w->signoff_worker_id/signature") }}' alt='Signature Unavailable'/></div>  
                    <?php $count++; ?>
                @endforeach
                @if ($count == 0)
                    <ul><li>N/A</li></ul>
                @endif
            </div>
        </div>
        <div class='col-md-6'>
            <div class="section-header-label"> Visitors </div>
            <div class="content-description"> 
                <?php $count = 0; ?>
                @foreach ( $flha->signoffVisitors as $v )
                    <div> {{ $v->first_name.' '.$v->last_name }} <img id='signaturePic' src='{{ URL::to("image/flha/visitor/$v->signoff_visitor_id/signature") }}' alt='Signature Unavailable'/> <!--<a href="#" class="pull-right list-component-link see-visitor-link " id="see_visitor_{{$v->signoff_visitor_id}}"> see signature</a> --> </div>
                    <?php $count++; ?>
                @endforeach
                @if ($count == 0)
                    <ul><li>N/A</li></ul>
                @endif
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class="section-header-label"> Spotchecks </div>
        </br>
        <?php $count = 0; ?>
        @foreach ( $flha->spotchecks as $s )
            <div class='row well whiteBackground'>
                <div class='col-md-10'>
                    <div class='col-md-6'>
                        <div> 
                            Created at: 
                            <ul><li><strong>{{ $s->created_at }}</strong></li></ul>
                        </div>
                        <div> 
                            Performed by:
                            <ul><li><strong>{{ $s->first_name.' '.$s->last_name }}</strong></li></ul>
                        </div>
                        <div> 
                            Company:
                            <ul><li><strong>{{ $s->company }}</strong></li></ul>
                        </div>
                        <div> 
                            Position:
                            <ul><li><strong>{{ $s->position }}</strong></li></ul>
                        </div>
                        <div>
                            {{ Form::label('profile_photo', 'Signature:', array('class'=>'control-label')) }}
                            <img id='signaturePic' src='{{ URL::to("image/flha/spotcheck/$s->spotcheck_id/signature") }}' alt='Signature Unavailable'/>
                        </div> 
                    </div>

                    <div class='col-md-6'>
                        <div>
                            Is the current FLHA valid for the tasks?:
                            <ul><li><strong>{{$s->flha_validity? 'Yes':'No'}}</strong></li></ul>
                        </div>

                        @if(!$s->flha_validity)
                            <div id="validityDescContainer">
                                FLHA validity description:
                                <ul><li><strong>{{$s->flha_validity_description}}</strong></li></ul>
                            </div>  
                        @endif

                        <div>
                            Critical hazard identified:
                            <ul><li><strong>{{$s->critical_hazard? 'Yes':'No'}}</strong></li></ul>
                        </div>

                        @if(!$s->critical_hazard)
                            <div>
                                Critical hazard description:
                                <ul><li><strong>{{$s->critical_hazard_description}}</strong></li></ul>
                            </div>
                        @endif

                        <div>
                            Crew list complete:
                            <ul><li><strong>{{$s->crew_list_complete? 'Yes':'No'}}</strong></li></ul>
                        </div>

                        @if(!$s->crew_list_complete)
                            <div>
                                Crew list description:
                                <ul><li><strong>{{$s->crew_description}}</strong></li></ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <?php $count++; ?>
        @endforeach
        @if ($count == 0)
            <ul><li><strong>N/A</strong></li></ul>
        @endif
    </div>

    @if ($flha->completion instanceOf JobCompletion)
    <div class='row well whiteBackground'>
        <div class='col-md-12'><div class="section-header-label"> Job Completion </div></div>
            <div class="col-md-4">    
                <div class="content-label"> Permit closed? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->permit_closed?"Yes":"No" }}</li></ul> </div>
                @if (!$flha->completion->permit_closed)           
                    <textarea class='form-control' rows=4 placeholder='Is the permit closed?' maxlength=400 data-bind='value: permit_closed_description' required></textarea>
                @endif

                <div class="content-label"> Hazard remaining? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->hazard_remaining?"Yes":"No" }}</li></ul> </div>
                @if($flha->completion->hazard_remaining)
                    <textarea class='form-control' rows=4 placeholder='Describe the hazard(s) remaining' maxlength=400 data-bind='value: hazard_remaining_description' required></textarea>
                @endif
            </div>

            <div class='col-md-4'>
                <div class="content-label"> Flagging removed? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->flagging_removed?"Yes":"No" }}</li></ul> </div>
                @if(!$flha->completion->flagging_removed)
                    <textarea class='form-control' rows=4 placeholder='Describe why the flagging was not removed' maxlength=400 data-bind='value: flagging_removed_description' required></textarea>
                @endif

                <div class="content-label"> Concerns addressed and documented? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->concerns?"Yes":"No" }}</li></ul> </div>
                @if(!$flha->completion->concerns)
                    <textarea class='form-control' rows=4 placeholder='Describe why concerns were not addressed and documented' maxlength=400 data-bind='value: concerns_description' required></textarea>
                @endif
            </div>

            <div class='col-md-4'>
                <div class="content-label"> Tools/equipment removed? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->equipment_removed?"Yes":"No"}}</li></ul> </div>
                @if(!$flha->completion->equipment_removed)
                    <textarea class='form-control' rows=4 placeholder='Describe why tools/equipment were not removed' maxlength=400 data-bind='value: equipment_removed_description' required></textarea>
                @endif

                <div class="content-label"> All incidents/injuries reported? </div>
                <div class="content-description"> <ul><li>{{ $flha->completion->incidents_reported?"Yes":"No" }}</li></ul> </div>
                @if(!$flha->completion->incidents_reported)
                    <textarea class='form-control' rows=4 placeholder='Describe why incidents/injuries were not reported' maxlength=400 data-bind='value: incidents_reported_description' required></textarea>
                @endif
            </div> 
        </div>


        <div class='row well whiteBackground'>
            <div class='col-md-4'>
                <div class="section-header-label"> Performed By </div>
                <div class="details-content">
                    <div class="content-label"> Worker name  </div>
                    <div class="content-description"> {{ $flha->addedBy->first_name.' '.$flha->addedBy->last_name }} </div>
                </div>
                <div class="details-content">
                    <div class="content-label"> Signature  </div>
                    @if ($flha->addedBy->signature())
                        <div class="content-description center"> 
                            <img src="{{ URL::to('image/worker/signature',$flha->addedBy->auth_token) }} " class="signature-image" alt='Signature Unavailable'>
                        </div>
                    @else
                        <div class="content-description"> 
                            No signature available
                        </div>
                    @endif
                </div>
            </div>
    {{ Form::close() }}
            <div class='col-md-8'>
                <div class="section-header-label"> Review by Management</div>
                
                @if (!$flha->review)
                    <form id="review-details" class="hidden">
                        <input type="text" name="resource_type" value="{{ get_class($flha) }}"/>
                        <input type="text" name="resource_id" value="{{ $flha->flha_id }}"/>
                    </form>
                    <div class="content-label" style="padding-right:5px;">
                        <button type='button' class="btn btn-info" id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                    </div>
                @else
                    <div class="details-content">
                        <div class="content-label"> Reviewed by: 
                            <span class="content-description"> {{ $flha->review->reviewer_name }} </span>
                            <br/>
                            On:
                            <span class="content-description"> {{ WKSSDate::display($flha->review->ts,$flha->review->created_at) }} </span>
                        </div>
                    </div>
                    <div class="details-content">
                        <div class="content-label"> Signature  </div>
                        @if ($flha->review->signature())
                            <div class="content-description center"> 
                                <img src="{{ URL::to('image/review/signature',$flha->review->form_review_id ) }}" class="admin-signature-image" alt='Signature Unavailable'>
                            </div>
                        @else
                            <div class="content-description"> 
                                No signature available
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class='row well whiteBackground'>
            <div class="section-header-label"> Photos</div>
            <br/>
            @if ($flha->photos()->count())
                <div class="nivoSlider photo-slider">
                    @foreach ($flha->photos as $photo) 
                        <a href="{{ Photo::generic($photo->name) }}" data-lightbox="flha">
                            <img src="{{ Photo::generic($photo->name) }}" />
                        </a>
                    @endforeach 
                </div>
            @else
                <div class="photo-slider">
                    <img src="{{ URL::to('image/no-photo')}}" class=''>
                </div>
            @endif
        </div>
    </div>
    @endif
</div> <!-- container -->
@endif
@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
  <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  <script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/flha-details.js') }}}"></script>
  <script>
      $(function(){
          $("#flhaAuditLogTable").dataTable();
      });
  </script>
  <script type="text/javascript">
      // Declarations which will be used for Knockout.JS
      var theFlha =         {{ json_encode($flha) }} ;
      var permits =         {{ json_encode($flha->permits()->get()->all()) }} ;
      var locations =       {{ json_encode($flha->locations()->get()->all()) }} ;
      var lsds =            {{ json_encode($flha->lsds()->get()->all()) }} ;
      var sites =           {{ json_encode($flha->sites()->get()->all()) }} ;
      var tasks =           {{ json_encode($flha->tasks) }};
  </script>
@stop
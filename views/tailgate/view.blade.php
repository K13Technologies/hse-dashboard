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
        .deleteItemBtn { 
            cursor: pointer; 
        }
        #tailgateTitle {
            width: 400px;
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
        .wideTextBox {
            width: 90%;
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
        #tailgateTitle {
            width: 100%;
        }
    </style>

@stop

@section('content')
<!-- START MODALS !-->
<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 id="modalEmailExport"> Email this Tailgate Card</h4>
                </div>
                <div class="modal-body">
                    <h5> Please enter an email address (multiple separated by commas):</h5>
                    {{ Form::text('tailgate_id',$tailgate->tailgate_id,array('class'=>'hide')) }}
                    {{ Form::textarea('email','',array('placeholder'=>'email@address.com','rows'=>'3','class'=>'form-control','id'=>'email')) }}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Cancel</button>
                    {{ Form::button('Send',array('class'=>'btn btn-orange medium pull-right','id'=>'completeExportToMail')) }}
                    <span id='mailError' style="color:red"> </span>
                </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
<!-- END MODALS !-->


<!-- START VISIBLE CONTENT !-->

<div class='container'>
    @if(isset($tailgate))
    {{ Form::model($tailgate, array('id'=>'editTailgateForm')) }}  
    {{ Form::hidden('tailgate_id') }}  

    <div class='row well whiteBackground'>
        <div class='col-md-8'>
            <span class="list-component">Tailgate Title: {{ Form::text('title', NULL, ['required', 'maxlength'=>'100', 'id'=>'tailgateTitle', 'class'=>'form-control']) }}</span>
        </div>
        <div class='col-md-4'>
            <span>
                <input href="#" type="submit" value="Save Edits" class="editTailgateButton btn btn-success"/>
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
                <a href='{{ URL::to("tailgates/export",array($tailgate->tailgate_id)) }}'> 
                    <button class="btn btn-orange small" id="exportPDFButton" type='button'>
                        Export to PDF
                        <i class="glyphicon glyphicon-file"></i>
                    </button>
                </a>
            </span> 
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-3'>
            <div class='row'>
                <div class="section-header-label"> Creation Date</div>
                <span title="Created on server at: {{ $tailgate->created_at }}" class="content-description"> 
                    {{ WKSSDate::display($tailgate->ts,$tailgate->created_at) }} 
                </span>  
            </div>    
            <div class="row">
                <?php 
                    // Normally this would be a big no-no, but the original devs put in the definition of the assessment "id" in the Tailgate model itself instead of a database table
                    $assessmentTypes = array();
                    $assessmentTypes[1]='Hazard Assessment';
                    $assessmentTypes[2]='Pre-Job';
                    $assessmentTypes[3]='Tailgate Meeting';
                    $assessmentTypes[4]='Safety Meeting';

                    // Sorts, but keeps IDs in tact
                    asort($assessmentTypes);    
                ?>
                
                <div class="section-header-label">Type of Assessment</div>
                <div class="content-label"> Currently selected type:  </div>
                <div class="content-description"> 
                    <ul><li>{{ $tailgate->getTypeOfAssessment() }} </li></ul>
                </div>

                {{ Form::select('assessment_type', $assessmentTypes, $tailgate->assessment_type, ['class'=>'form-control']) }}
            </div>
            <div class="row"> 
                <div class="section-header-label"> Project Management </div>
                </br></br></br></br></br>
            </div>
        </div>
        <div class='col-md-1'></div>
        <div class='col-md-7'>
            <div class='row'>
                <div class='col-md-12'>
                    <div class="section-header-label"> Job Description </div>
                    </br>
                    {{ Form::textarea('job_description', NULL, array('rows'=>3,'class'=>'action-description wideTextBox form-control', 'placeholder'=>'Description for this tailgate', 'required', 'maxlength'=>'400')) }}
                    </br>
                </div>
            </div>

            <div class='row'>        
                <div class='row'>
                    <div class='col-md-12'>
                        <div class='col-md-12'>
                            <div class="section-header-label"> Tailgate Details </div>
                        </div>
                        <div class='col-md-4'>
                            {{ Form::label('job_number', 'Job Number', ['class' => 'content-label']) }}
                            <div class="content-description"> {{ Form::text('job_number', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }} </div>
                        </div>
                        <div class='col-md-4'>
                            {{ Form::label('phone_number', 'Phone Number', ['class' => 'content-label']) }}
                            <div class="content-description"> {{ Form::text('phone_number', NULL, ['maxlength'=>'100', 'placeholder'=>'###-###-####', 'pattern'=>'\d\d\d-\d\d\d-\d\d\d\d', 'title'=>'###-###-###', 'class'=>'form-control']) }} </div>
                        </div>
                        <div class='col-md-4'>
                            {{ Form::label('stars_site', 'STARS Site', ['class' => 'content-label']) }}
                            <div class="content-description"> {{ Form::number('stars_site', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }} </div>
                        </div>
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
                            <p class="section-header-label"> Supervisors </p>
                        </div>
                        <div class='col-md-12'>
                            <button type="button" class="btn btn-sm btn-primary" data-bind='click: addSupervisor'>Add Supervisor</button>
                            <br/><br/>
                        </div>
                        <div class='col-md-12' data-bind='foreach: supervisors'>  
                            <div class='col-md-10'>
                                <input class='form-control' type="text" data-bind="attr: {'name':'supervisors['+ $index()+']'}, value: name" required placeholder="Supervisor name" maxlength="100"></input>
                            </div>
                            <div class='col-md-2'>
                                <i class="glyphicon glyphicon-trash deleteItemBtn" data-bind='visible: $root.supervisors().length > 1, click: $root.removeSupervisor'></i>
                                <br/><br/>
                            </div>
                        </div>
                    </div>
                </div>


                <div class='row'>
                    <div class='col-md-12'>
                        <div class='col-md-12'>
                            <p class="section-header-label"> Permit Number </p>
                        </div>
                        <div class='col-md-12'>
                            <button type="button" class="btn btn-sm btn-primary" data-bind='click: addPermitNumber'>Add Permit #</button>
                            <br/><br/>
                        </div>
                        <div class='col-md-12' data-bind='foreach: permitNumbers'>  
                            <div class='col-md-10'>
                                <input class='form-control' type="text" data-bind="attr: {'name':'permits['+ $index()+']'}, value: permit_number" placeholder="Permit Number" maxlength="100" required></input>
                            </div>
                            <div class='col-md-2'>
                                <i class="glyphicon glyphicon-trash deleteItemBtn" data-bind='visible:$root.permitNumbers().length > 1, click: $root.removePermitNumber'></i>
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
            </div>
        </div>
    </div> <!-- well -->


    <div class='row well whiteBackground'>
        <div class='row'>
            <div class='col-md-12'><div class="section-header-label"> Assessment Checklist </div></div>
            <div class='col-md-4'> 
                <div class="details-content">
                    <div class="content-label"> Is everyone fit for duty? </div>
                    <div class="content-description">  <ul><li>{{ $tailgate->fit_for_duty?"Yes":"No"}}</li></ul></div>
                    @if(!$tailgate->fit_for_duty)
                        {{ Form::textarea('fit_for_duty_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Fit for duty description', 'maxlength'=>'400', 'required')) }}
                    @endif

                    <div class="content-label"> Is everyone properly trained/qualified to do their job? </div>
                    <div class="content-description">  <ul><li>{{ $tailgate->proper_training?"Yes":"No"}}</li></ul></div>
                    @if(!$tailgate->proper_training)
                        {{ Form::textarea('proper_training_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Properly trained/qualified description', 'maxlength'=>'400', 'required')) }}
                    @endif
                </div>
            </div>
            <div class='col-md-4'>
                <div class="content-label"> Has the job scope and procedure(s) have been discussed with everyone on location? </div>
                <div class="content-description">  <ul><li>{{ $tailgate->job_scope_and_procedures?"Yes":"No"}}</li></ul></div>
                @if(!$tailgate->job_scope_and_procedures)
                    {{ Form::textarea('job_scope_and_procedures_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Job scope and procedure discussion description', 'maxlength'=>'400', 'required')) }}
                @endif

                <div class="content-label"> Have the hazards specific to the job been identified? </div>
                <div class="content-description">  <ul><li>{{ $tailgate->hazards_identified?"Yes":"No"}}</li></ul></div>
                @if (!$tailgate->hazards_identified)
                    {{ Form::textarea('hazards_identified_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Have the hazards specific to the job been identified? ', 'maxlength'=>'400', 'required')) }}
                @endif
            </div>
            <div class='col-md-4'>
                <div class="content-label"> Have the required hazard controls been implemented and confirmed? </div>
                <div class="content-description">  <ul><li>{{ $tailgate->controls_implemented?"Yes":"No"}}</li></ul></div>
                @if(!$tailgate->controls_implemented)
                    {{ Form::textarea('controls_implemented_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Have the hazards specific to the job been identified? ', 'maxlength'=>'400', 'required')) }}
                @endif

                {{ Form::label('comment', 'Additional Comments', ['class' => 'content-label']) }}
                {{ Form::textarea('comment', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Additional comments', 'maxlength'=>'400')) }}             
            </div>
        </div>

        <div class='row'>
            <div class='col-md-12'><div class="section-header-label"> Hazard Checklist </div></div>

            @foreach ($tailgate->checklist as $categoryId => $hazardList)
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
                    <input type='hidden' data-bind='value: tailgate_task_id, attr: {"name":"tasks[" + $index() + "][tailgate_task_id]"}'/>

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
                                    <input type='hidden' data-bind='value: tailgate_task_hazard_id, attr: {"name":"tasks["+ $parentContext.$index()+"][hazards]["+$index()+"][tailgate_task_hazard_id]"}'/>
                                    
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
                @foreach ( $tailgate->signoffWorkers as $w )
                    <div> {{ $w->first_name.' '.$w->last_name }} <img class='signature-image' src='{{ URL::to("image/tailgates/worker/$w->signoff_worker_id/signature") }}' alt='Signature Unavailable'/></div>
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
                @foreach ( $tailgate->signoffVisitors as $v )
                    <div> {{ $v->first_name.' '.$v->last_name }} <img class='signature-image' src='{{ URL::to("image/tailgates/visitor/$v->signoff_visitor_id/signature") }}' alt='Signature Unavailable'/> </div>
                    <?php $count++; ?>
                @endforeach
                @if ($count == 0)
                    <ul><li>N/A</li></ul>
                @endif
            </div>
        </div>
    </div>


    @if ($tailgate->completion instanceOf JobCompletion)
    <div class='row well whiteBackground'>
        <div class='col-md-12'><div class="section-header-label"> Job Completion </div></div>
            <div class="col-md-4">    
                <div class="content-label"> Permit closed? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->permit_closed?"Yes":"No" }}</li></ul> </div>
                @if (!$tailgate->completion->permit_closed)
                    {{ Form::textarea('permit_closed_description', $tailgate->completion->permit_closed_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Is the permit closed?', 'maxlength'=>'400')) }}             
                @endif

                <div class="content-label"> Hazard remaining? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->hazard_remaining?"Yes":"No" }}</li></ul> </div>
                @if($tailgate->completion->hazard_remaining)
                    {{ Form::textarea('hazard_remaining_description', $tailgate->completion->hazard_remaining_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Hazard remaining?', 'maxlength'=>'400')) }}            
                @endif
            </div>

            <div class='col-md-4'>
                <div class="content-label"> Flagging removed? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->flagging_removed?"Yes":"No" }}</li></ul> </div>
                @if(!$tailgate->completion->flagging_removed)
                    {{ Form::textarea('flagging_removed_description', $tailgate->completion->flagging_removed_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Flagging removed?', 'maxlength'=>'400')) }}             
                @endif

                <div class="content-label"> All incidents/injuries reported? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->incidents_reported?"Yes":"No" }}</li></ul> </div>
                @if(!$tailgate->completion->incidents_reported)
                    {{ Form::textarea('incidents_reported_description', $tailgate->completion->incidents_reported_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'All incidents/injuries reported?', 'maxlength'=>'400')) }}             
                @endif
            </div>

            <div class='col-md-4'>
                <div class="content-label"> Concerns addressed and documented? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->concerns?"Yes":"No" }}</li></ul> </div>
                @if(!$tailgate->completion->concerns)
                    {{ Form::textarea('concerns_description', $tailgate->completion->concerns_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Concerns addressed and documented?', 'maxlength'=>'400')) }}             
                @endif

                <div class="content-label"> Tools/equipment removed? </div>
                <div class="content-description"> <ul><li>{{ $tailgate->completion->equipment_removed?"Yes":"No"}}</li></ul> </div>
                @if(!$tailgate->completion->equipment_removed)
                    {{ Form::textarea('equipment_removed_description', $tailgate->completion->equipment_removed_description, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Tools/equipment removed?', 'maxlength'=>'400')) }}             
                @endif
            </div>  
    </div>
    @endif

    <div class='row well whiteBackground'>
        <div class='col-md-4'>
            <div class="section-header-label"> Performed By </div>
            <div class="details-content">
                <div class="content-label"> Worker name  </div>
                <div class="content-description"> {{ $tailgate->addedBy->first_name.' '.$tailgate->addedBy->last_name }} </div>
            </div>
            <div class="details-content">
                <div class="content-label"> Signature  </div>
                @if ($tailgate->addedBy->signature())
                    <div class="content-description center"> 
                        <img src="{{ URL::to('image/worker/signature',$tailgate->addedBy->auth_token) }}" class="signature-image" alt='Signature Unavailable'>
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
            
            @if (!$tailgate->review)
                <form id="review-details" class="hidden">
                    <input type="text" name="resource_type" value="{{ get_class($tailgate) }}"/>
                    <input type="text" name="resource_id" value="{{ $tailgate->tailgate_id }}"/>
                </form>
                <div class="content-label" style="padding-right:5px;">
                    <button type='button' class="btn btn-info" id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                </div>
            @else
                <div class="details-content">
                    <div class="content-label"> Reviewed by: 
                        <span class="content-description"> {{ $tailgate->review->reviewer_name }} </span>
                        <br/>
                        On:
                        <span class="content-description"> {{ WKSSDate::display($tailgate->review->ts,$tailgate->review->created_at) }} </span>
                    </div>
                </div>
                <div class="details-content">
                    <div class="content-label"> Signature  </div>
                    @if ($tailgate->review->signature())
                        <div class="content-description center"> 
                            <img src="{{ URL::to('image/review/signature',$tailgate->review->form_review_id ) }}" class="admin-signature-image" alt='Signature Unavailable'>
                        </div>
                    @else
                        <div class="content-description"> 
                            No signature available
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div> <!-- well -->
</div> <!-- Container -->           
@endif
@stop

@section('scripts')
  @parent 
    <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
    <script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/tailgate-details.js') }}}"></script>
    <script>
        $(function(){
            $("#tailgateAuditLogTable").dataTable();
        });
    </script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/knockout-validation/2.0.2/knockout.validation.min.js'></script>
    <script type="text/javascript">
        // Declarations which will be used for Knockout.JS
        var originalSupervisors = <?php echo json_encode($tailgate->supervisors()->get()->all()); ?> ;
        var originalPermits =     <?php echo json_encode($tailgate->permits()->get()->all());     ?> ;
        var originalLocations =   <?php echo json_encode($tailgate->locations()->get()->all())    ?> ;
        var originalLSDs =        <?php echo json_encode($tailgate->lsds()->get()->all());        ?> ;
        var originalTasks =       <?php echo json_encode($tailgate->tasks)                        ?> ;
    </script>
@stop
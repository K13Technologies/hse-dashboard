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
        .personWell {
          min-height: 20px;
          margin: 10px;
          padding: 5px;
          background-color: #f5f5f5;
          border: 1px solid #e3e3e3;
          -webkit-border-radius: 4px;
          -moz-border-radius: 4px;
          border-radius: 4px;
          -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
          -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
          box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.05);
        }
        #selectHelpButton { 
            cursor: pointer; 
        }
        #exportPDFButton {
            margin-left: -9px;
        }
        .photo-slider img{
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
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="modalEmailExport"> Email this Field Observation Card</h4>
            </div>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <div class="modal-body">
                    <h5> Please enter an email address (multiple separated by commas):</h5>
                    {{ Form::text('positive_observation_id',$po->positive_observation_id,array('class'=>'hide')) }}
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

<div class='container'>
@if(isset($po))
{{ Form::model($po, array('id'=>'editObservationForm')) }}  
{{ Form::hidden('positive_observation_id', $po->positive_observation_id) }}

    <div class='row well whiteBackground'>
        <div class='col-md-8'>
            <span class="list-component">Field Observation Title: {{ Form::text('title', NULL, ['required', 'maxlength'=>'100','class'=>'form-control']) }}</span>
        </div>
        <div class='col-md-4'>
            <span>
                <input href="#" type="submit" value="Save Edits" class="editObservationButton btn btn-success"/>
            </span>
            <span class="export-button">
                <a href="#modalEmailSave" data-toggle="modal">
                    <button class="btn btn-orange btn-sm small">
                        Send via email
                        <i class="glyphicon glyphicon-envelope"></i>
                    </button>
                </a>
            </span>
            <span class="export-button">  
                <button class="btn btn-orange btn-sm small" id="exportPDFButton">
                    Export to PDF
                    <i class="glyphicon glyphicon-file"></i>
                </button>
            </span>  
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-2'>
            <div class='row'>
                <div class="section-header-label"> Creation Date </div>
                <span title="{{ $po->created_at }}"> 
                    {{ WKSSDate::display($po->ts, $po->created_at) }} 
                </span> 
            </div>

            <div class='row'>
                <div class="section-header-label"> Location </div>

                <div clas='row'>
                    {{ Form::label('site', 'Site', ['class' => 'content-label']) }}
                    {{ Form::text('site', NULL, ['required', 'maxlength'=>'100', 'class'=>'form-control']) }}

                    {{ Form::label('specific_location', 'Specific Location', ['class' => 'content-label']) }}
                    {{ Form::text('specific_location', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }}

                    {{ Form::label('lsd', 'LSD (Format: ###/##-##-###-##-W#M)', ['class' => 'content-label']) }}
                    {{ Form::text('lsd', NULL, ['maxlength'=>'30','class'=>'form-control', 'placeholder'=>'###/##-##-###-##-W#M', 'pattern'=>'\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM', 'title'=>'###/##-##-###-##-W#M']) }}

                    {{ Form::label('wellpad', 'Wellpad', ['class' => 'content-label']) }}
                    {{ Form::text('wellpad', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }}
                </div>
            </div>

            <div class='row'>
                <div class="section-header-label"> Persons Observed </div>
                <div data-bind='foreach: people'>
                    <div class="personWell">  
                        <label data-bind="attr: {'for':'personsObserved['+ $index()+'][name]'}" class="content-label">Name</label> 
                        <input type="text" class='form-control' data-bind="attr: {'name':'personsObserved['+ $index()+'][name]'}, value: name" required placeholder="Person Name" maxlength="100"></input>

                        <label data-bind="attr: {'for':'personsObserved['+ $index()+'][company]'}" class="content-label">Company</label>
                        <input type="text" class='form-control'  data-bind="attr: {'name':'personsObserved['+ $index()+'][company]'}, value: company" required placeholder="Company Name" maxlength="100"></input>
                        <br/>
                        <button type="button" class="btn btn-xs btn-warning" data-bind='visible: $root.people().length > 1, click: $root.removePerson'>Delete</button>
                    </div> 
                </div>   
                <button type="button" class="btn btn-sm btn-primary" data-bind='click: addPerson'>Add Person</button>
            </div>  
        </div>

        <div class='col-md-1'></div> 

        <div class='col-md-3'>
            <div class='row'>
                <div class="section-header-label"> Activity </div>
                </br>
                <?php
                    // Get associative array
                    $activities = PositiveObservationActivity::lists('activity_name', 'positive_observation_activity_id');
                    // Sorts, but keeps IDs in tact
                    asort($activities);
                ?>

                {{ Form::select('positive_observation_activity_id', $activities, $po->activity->positive_observation_activity_id, ['class'=>'form-control']) }}
                </div>

                <div class='row'>
                    <div class="section-header-label"> Tasks Observed </div>

                    <div id='taskContainer'>
                        @if ($po->task_1_title || $po->task_1_description)
                          <div class="taskObject">
                              <div class="content-numbering pull-left"> 1 </div>
                              <div class="pull-left numbered-content">
                                  <div class="content-label">{{ Form::label('task_1_title', 'Title', ['class' => 'content-label']) }}</div>
                                  <div class="content-description"> 
                                      {{ Form::text('task_1_title', NULL, ['maxlength'=>'100', 'required', 'class'=>'form-control']) }} 
                                  </div>
                                  <div class="content-label">{{ Form::label('task_1_description', 'Description', ['class' => 'content-label']) }}</div>
                                  <div class="content-description">
                                      {{ Form::textarea('task_1_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Description for this task', 'required', 'maxlength'=>'400')) }}
                                  </div>
                              </div>
                          </div>
                        @endif
                        
                        @if ($po->task_2_title || $po->task_2_description)
                          <div class="taskObject">
                              <div class="content-numbering pull-left"> 2 </div>
                              <div class="pull-left numbered-content">
                                  <div class="content-label">{{ Form::label('task_2_title', 'Title', ['class' => 'content-label']) }}</div>
                                  <div class="content-description"> 
                                      {{ Form::text('task_2_title', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }} 
                                  </div>
                                  <div class="content-label">{{ Form::label('task_2_description', 'Description', ['class' => 'content-label', 'required']) }}</div>
                                  <div class="content-description">
                                      {{ Form::textarea('task_2_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Description for this task', 'required', 'maxlength'=>'400')) }}
                                  </div>
                              </div>
                          </div>  
                        @endif
                        
                        @if ($po->task_3_title || $po->task_3_description)
                          <div class="taskObject">
                              <div class="content-numbering pull-left"> 3 </div>
                              <div class="pull-left numbered-content">
                                  <div class="content-label">{{ Form::label('task_3_title', 'Title', ['class' => 'content-label']) }}</div>
                                  <div class="content-description"> 
                                      {{ Form::text('task_3_title', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }} 
                                  </div>
                                  <div class="content-label">{{ Form::label('task_3_description', 'Description', ['class' => 'content-label', 'required']) }}</div>
                                  <div class="content-description">
                                      {{ Form::textarea('task_3_description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Description for this task', 'required', 'maxlength'=>'400')) }}
                                  </div>
                              </div>
                          </div>
                        @endif
                        <br/>
                    </div>
                    <button class="btn btn-sm btn-primary" type='button' id="btnAddTask">Add Task</button>
                    <button class="btn btn-sm btn-warning" type='button' id="btnRemoveTask">Remove Last Task</button>
                </div> <!-- Tasks Observed Row -->
            </div>

            <div class="col-md-3">
               <div class="section-header">
                    <div class="section-header-label"> Hazard Categories</div>
                    <div class="details-content">
                        <p class="content-label">Current Categories</p>
                        <ul>
                        @foreach ($po->positiveObservationCategories as $pc)
                            <li class="content-description"> {{ $pc->category_name }} </li>
                        @endforeach
                        </ul>

                        <?php 
                            // Full list
                            $hazardCategories = PositiveObservationCategory::lists('category_name','positive_observation_category_id');
                            asort($hazardCategories);

                            $selectedHazardCategories = array();

                            // Build the array of the hazards' current hazard categories
                            foreach($po->positiveObservationCategories as $category) {
                                array_push($selectedHazardCategories,$category["positive_observation_category_id"]);
                            }
                        ?>

                        <p> <p class="content-label">Modify Categories <i id="selectHelpButton" class="glyphicon glyphicon-question-sign"></i></p> </p>

                        {{Form::select('categoryIds[]', $hazardCategories, $selectedHazardCategories, array('multiple'=>true,'class'=>'form-control', 'required')); }}
                    </div>
                </div>  
                <div class="section-header">
                    <div class="section-header-label"> Observations </div>
                    <div class="details-content">
                        <div class="content-label"> Is this a positive observation?</div>
                        <div class="content-description">{{ Form::checkbox('is_positive_observation', NULL, NULL, array('class'=>'actionSlider', 'id'=>'positiveObservationSwitch')) }}</div>
                    @if ($po->is_positive_observation)
                        <div class="content-label" id="isPODetailsLabel">Positive observation description</div>
                        {{ Form::textarea('is_po_details', NULL, array('rows'=>4, 'maxlength'=>'400', 'required', 'class'=>'form-control')) }}
                        
                        <div id="correctAtRiskContainer" hidden>
                            <div class="content-label">Correct at risk behaviour on site? </div>
                            <div class="content-description"> {{ Form::checkbox('correct_on_site', NULL, NULL, array('class'=>'actionSlider', 'disabled')) }} </div>
                        </div>
                    @else
                        <div class="content-label" id="isPODetailsLabel">Corrective action for at risk behaviour</div>
                        <div class="content-description"> {{ Form::textarea('is_po_details', NULL, array('rows'=>4,  'maxlength'=>'400', 'class'=>'form-control', 'required')) }} </div>
                        
                        <div id="correctAtRiskContainer">
                            <div class="content-label">Correct at risk behaviour on site? </div>
                            <div class="content-description"> {{ Form::checkbox('correct_on_site', NULL, NULL, array('class'=>'actionSlider')) }} </div>
                        </div>
                    @endif

                    <div class="content-label"> Completed on </div>
                    {{ Form::text('completed_on', NULL, array('class'=>'action-date-picker form-control','placeholder'=>'yyyy-mm-dd', 'pattern'=>'\d\d\d\d-\d\d-\d\d'))}}
                    <div class="content-label"> Required Action </div>
                    {{ Form::textarea('action', NULL ,array('rows'=>4, 'class'=>'action-description form-control','placeholder'=>'Describe the required action for this item', 'maxlength'=>'500')) }}
                    </div>
                </div>  
                <div class="section-header">
                    <div class="section-header-label"> Comments</div>
                    <div class="details-content">
                        </br>
                        <div class="content-description"> {{ Form::textarea('comment', NULL, array('rows'=>4, 'placeholder'=>'Comments about this observation','class'=>'form-control', 'maxlength'=>'400')) }} </div>
                    </div>
                </div> 
            </div>

            <div class='col-md-3'>
{{ Form::close() }} 
                <div class="section-header">
                    <div class="section-header-label"> Performed By </div>
                    <div class="details-content">
                        <div class="content-label"> Worker name  </div>
                        <div class="content-description"> {{ $po->addedBy->first_name.' '.$po->addedBy->last_name }} </div>
                    </div>
                    <div class="details-content">
                        <div class="content-label"> Signature  </div>
                        @if ($po->addedBy->signature())
                            <div class="content-description center"> 
                                <img src="{{ URL::to('image/worker/signature',$po->addedBy->auth_token) }} " class="signature-image">
                            </div>
                        @else
                            <div class="content-description"> 
                                No signature available
                            </div>
                        @endif
                    </div>
                </div>

                <div class="section-header">
                    <div class="section-header-label"> Review by management</div>
                    
                    @if (!$po->review)
                        <form id="review-details" class="hidden">
                            <input type="text" name="resource_type" value="{{ get_class($po) }}"/>
                            <input type="text" name="resource_id" value="{{ $po->positive_observation_id }}"/>
                        </form>
                        <div class="content-label" style="padding-right:5px;">
                            <button class="btn btn-info" type='button' id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                        </div>
                    @else
                        <div class="details-content">
                            <div class="content-label"> Reviewed by: 
                                <span class="content-description"> {{ $po->review->reviewer_name }} </span>
                                <br/>
                                On:
                                <span class="content-description"> {{ WKSSDate::display($po->review->ts,$po->review->created_at) }} </span>
                            </div>
                        </div>
                        <div class="details-content">
                            <div class="content-label"> Signature  </div>
                            @if ($po->review->signature())
                                <div class="content-description center"> 
                                    <img src="{{ URL::to('image/review/signature',$po->review->form_review_id ) }}" class="admin-signature-image">
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
        </div>
        
    <div class='row well whiteBackground'>
        <div class="section-header-label"> Photos </div>
        <br/>
        @if ($po->photos()->count())
            <div class="nivoSlider photo-slider">
                @foreach ($po->photos as $photo) 
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

@endif
</div> <!-- container -->     
@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
  <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  <script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/positive-observation-details.js') }}}"></script>
  <script src="{{{ asset('assets/js/bootstrap-checkbox.min.js') }}}" ></script>
  <script>
    $(function(){
        $("#observationAuditLogTable").dataTable();
        $('.actionSlider').checkboxpicker();
    });
  </script>
  <script type="text/javascript">var originalPeopleObserved = <?php echo json_encode($po->personsObserved); ?>;</script>
@stop
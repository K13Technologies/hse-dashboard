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

        #exportPDFButton {
            /*margin-top: -4px;*/
            margin-left: -9px;
        }

        #selectHelpButton { 
            cursor: pointer; 
        }

        .photo-slider img {
            width:100%;
        }
        .photo-slider a img {
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
              <h4 id="modalEmailExport"> Email this Near Miss Card</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                    <h5> Please enter an email address (multiple separated by commas):</h5>
                    {{ Form::text('near_miss_id',$nearMiss->near_miss_id,array('class'=>'hide')) }}
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

<!-- STATUS BOX !-->
<div id="formErrorsContainer" class="list-container hidden">
    <div class="list-container-header">
        <span class="list-component">Form Errors</span>
    </div>

    <div id="formErrorsList" class="list-container-body row-fluid">

    </div>
</div>
<!-- STATUS BOX !-->


<!-- START VISIBLE CONTENT !-->  

<div class='container'>
@if(isset($nearMiss))
{{ Form::model($nearMiss, array('id'=>'editNearMissForm')) }}
    <div class='row well whiteBackground'>
        <div class='col-md-8'>
            <span class="list-component">Title: {{ Form::text('title', NULL, ['required', 'maxlength'=>'100', 'class'=>'form-control']) }}</span>
        </div>
        <div class='col-md-4'>
            <span class="">
                <input href="#" type="submit" value="Save Edits" class="editNearMissButton btn btn-sm btn-success"/>
            </span> 
            <span class="export-button">
                <a href="#modalEmailSave" data-toggle="modal">
                    <button class="btn btn-orange small">
                        Send via email
                        <i class="glyphicon glyphicon-envelope"></i>
                    </button>
                </a>
            </span>
            <span class="export-button">
                {{-- <a href='{{ URL::to("near-misses/export",array($nearMiss->near_miss_id)) }}'> --}}
                    <button class="btn btn-orange small" id="exportPDFButton">
                        Export to PDF
                        <i class="glyphicon glyphicon-file"></i>
                    </button>
                {{-- </a> --}}
            </span>
        </div>
    </div>

{{ Form::hidden('near_miss_id', $nearMiss->near_miss_id) }}
    <div class='row well whiteBackground'>
        <div class='col-md-3'>
            <div class='row'>
                <div class="section-header-label"> Creation Date </div>
                <span title="{{ $nearMiss->created_at }}"> 
                    {{ WKSSDate::display($nearMiss->ts, $nearMiss->created_at) }} 
                </span>  
            </div>

            <div class='row'>
                <div class="section-header-label"> Location </div>
                {{ Form::label('site', 'Site', ['class' => 'content-label']) }}
                {{ Form::text('site', NULL, ['required', 'maxlength'=>'100', 'class'=>'form-control']) }}

                {{ Form::label('specific_location', 'Specific Location', ['class' => 'content-label']) }}
                {{ Form::text('specific_location', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }}

                <?php /*<input type="text" name="ssn"
                        pattern="^\d{3}-\d{2}-\d{4}$"
                        title="The Social Security Number" />*/   
                ?>
                {{ Form::label('lsd', 'LSD (Format: ###/##-##-###-##-W#M)', ['class' => 'content-label']) }}
                {{ Form::text('lsd', NULL, ['maxlength'=>'30', 'class'=>'form-control', 'placeholder'=>'###/##-##-###-##-W#M', 'pattern'=>'\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM', 'title'=>'###/##-##-###-##-W#M']) }}

                {{ Form::label('wellpad', 'Wellpad', ['class' => 'content-label']) }}
                {{ Form::text('wellpad', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }}

                {{ Form::label('road', 'Road or Intersection', ['class' => 'content-label']) }}
                {{ Form::text('road', NULL, ['maxlength'=>'100', 'class'=>'form-control']) }}
            </div>
        </div>
        <div class='col-md-1'></div>
        <div class='col-md-2'>
            <div class='row'>
                <div class="section-header-label"> Activity </div>
                </br>
                <?php
                    // Get associative array
                    $activities = HazardActivity::lists('activity_name', 'hazard_activity_id');
                    // Sorts, but keeps IDs in tact
                    asort($activities);
                ?>

                {{ Form::select('hazard_activity_id', $activities, $nearMiss->hazardActivity->hazard_activity_id, ['class'=>'form-control']) }}
            </div>

            <div class='row'>
                <div class="section-header-label"> Description </div>
                </br>
                {{ Form::textarea('description', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Description for this item', 'required', 'maxlength'=>'400')) }}
            </div>

            <div class='row'>
                <div class="section-header-label"> Hazard Categories </div>

                <p class="content-label">Current Categories</p>
                <ul>
                @foreach ($nearMiss->hazardCategories as $hc)
                    <li class="content-description"> {{ $hc->category_name }} </li>
                @endforeach
                </ul>

                <?php 
                    // Full list
                    $hazardCategories = HazardCategory::lists('category_name','hazard_category_id');
                    asort($hazardCategories);

                    $selectedHazardCategories = array();

                    // Build the array of the near misses' current hazard categories
                    foreach($nearMiss->hazardCategories as $hc) {
                        array_push($selectedHazardCategories,$hc["hazard_category_id"]);
                    }
                ?>

                <p> <p class="content-label">Modify Categories <i id="selectHelpButton" class="glyphicon glyphicon-question-sign"></i></p> </p>

                {{Form::select('hazard_category_ids[]', $hazardCategories, $selectedHazardCategories, array('multiple'=>true,'class'=>'form-control', 'required')); }}
            </div>
        </div>

        <div class='col-md-3'>
            <div class="section-header-label"> Corrective Action</div>
            <br/>
            {{ Form::textarea('corrective_action', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Corrective action description', 'required', 'maxlength'=>'400')) }}
        
            <div class="content-label">Corrective action applied? </div>
               {{-- {{ Form::checkbox('corrective_action_applied', 1, 1, array('class'=>'sliderCheckBox', 'id'=>'actionSlider', 'style'=>'width:40px;')) }}  --}}
            
               {{ Form::checkbox('corrective_action_applied', NULL, NULL, array('id'=>'actionSlider', 'style'=>'width:40px;')) }}
               </br>
            @if ($nearMiss->corrective_action_applied)    
                <div id="actionItemsContainer" hidden>
                    <div class="content-label"> Reason </div>
                    <div class="content-description"> {{ Form::textarea('corrective_action_implementation', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Describe why you were unable to implement the corrective action on the spot', 'maxlength'=>'400', 'required', 'disabled')) }} </div> 
                </div>  
            @else
                <div id="actionItemsContainer">
                    <div class="content-label"> Reason </div>
                    <div class="content-description"> {{ Form::textarea('corrective_action_implementation', NULL, array('rows'=>4,'class'=>'action-description form-control','placeholder'=>'Describe why you were unable to implement the corrective action on the spot', 'maxlength'=>'400', 'required')) }} </div>  
                </div>
            @endif

            <div class="content-label"> Completed on </div>
            {{ Form::text('completed_on', NULL, array('class'=>'action-date-picker form-control','placeholder'=>'yyyy-mm-dd', 'pattern'=>'\d\d\d\d-\d\d-\d\d'))}}
            <div class="content-label"> Required Action </div>
            {{ Form::textarea('action', NULL ,array('rows'=>4, 'class'=>'action-description form-control','placeholder'=>'Describe the required action for this item', 'maxlength'=>'500')) }}

            <div class="section-header-label"> Comments</div>
            </br>
            <div class="content-description"> {{ Form::textarea('comment', NULL, array('rows'=>4, 'class'=>'form-control', 'placeholder'=>'Comments about this item', 'maxlength'=>'400')) }} </div>
        </div>

        <div class='col-md-3'>
            <div class="section-header-label"> Performed by </div>
            <div class="details-content">
                <div class="content-label"> Worker name  </div>
                <div class="content-description"> {{ $nearMiss->addedBy->first_name.' '.$nearMiss->addedBy->last_name }} </div>
            </div>
            <div class="details-content">
                <div class="content-label"> Signature  </div>
                @if ($nearMiss->addedBy->signature())
                    <div class="content-description center"> 
                        <img src="{{ URL::to('image/worker/signature',$nearMiss->addedBy->auth_token) }} " class="signature-image">
                    </div>
                @else
                    <div class="content-description"> 
                        No signature available
                    </div>
                @endif
            </div>
{{ Form::close() }} 
@endif 
            <div class="section-header-label"> Review by Management</div>
            
            @if (!$nearMiss->review)
                <form id="review-details" hidden>
                    <input type="text" name="resource_type" value="{{ get_class($nearMiss) }}"/>
                    <input type="text" name="resource_id" value="{{ $nearMiss->near_miss_id }}"/>
                </form>
                <div class="content-label" style="padding-right:5px;">
                    <button class="btn btn-info" type='button' id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                </div>
            @else
                <div class="details-content">
                    <div class="content-label"> Reviewed by: 
                        <span class="content-description"> {{ $nearMiss->review->reviewer_name }} </span>
                        <br/>
                        On:
                        <span class="content-description"> {{ WKSSDate::display($nearMiss->review->ts,$nearMiss->review->created_at) }} </span>
                    </div>
                </div>
                <div class="details-content">
                    <div class="content-label"> Signature  </div>
                    @if ($nearMiss->review->signature())
                        <div class="content-description center"> 
                            <img src="{{ URL::to('image/review/signature',$nearMiss->review->form_review_id ) }}" class="admin-signature-image">
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
        <div class='col-md-12 section-header-label'> Photos </div>
        <br/><br/>
        @if ($nearMiss->photos()->count())
            <div class="nivoSlider photo-slider">
                @foreach ($nearMiss->photos as $photo) 
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
</div> <!-- container -->
@stop


@section('scripts')
  @parent 
  <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
  <script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
  <script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
  <script src="{{{ asset('assets/js/wkss/near-miss-details.js') }}}"></script>
  <script src="{{{ asset('assets/js/bootstrap-checkbox.min.js') }}}" ></script>

  <script>
    $(function(){
        $("#nearMissAuditLogTable").dataTable();

        // This is how you would apply it to all checkboxes
        // $(':checkbox').checkboxpicker();

        $('#actionSlider').checkboxpicker();
    });
  </script>
@stop
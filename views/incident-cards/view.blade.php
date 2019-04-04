@extends('webApp::layouts.withNav')
@section('styles')
@parent

<link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
<link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
<link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
<link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">

<link href="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/d004434a5ff76e7b97c8b07c01f34ca69e635d97/build/css/bootstrap-datetimepicker.css" rel="stylesheet">

<style>
    .row-fluid{
        margin-left:-10px!important;
    }
    .span3:first-child{
        padding-left:10px;
    }
    .span4:first-child, .span6{
        padding-left:10px;
    }
    .list-container-header.bordered{
        border-top: 1px solid #d7e3f0;
    }
    td>.content-description{
        padding-top:7px;
    }
    .incident-details-table{
        width:100%;
    }
    .incident-details-table td{
        /*padding: 0 10px;*/
    }
    .part-photo{
        width:100px;
    }
    .type-link{
        color:graytext;
    }
    #incidentTitle {
        width: 100%;
    }
    #exportPDFButton {
        margin-left: -9px;
    }
    #selectHelpButton { 
        cursor: pointer; 
    }
    .add-on  { 
        cursor: pointer; 
    }
    .whiteBackground {
        background-color: white;
    }
    .wideTextBox {
        width: 100%;
    }
    .photo-slider img {
        width: 100%;
    }
</style>
@stop

@section('content')
<!--=============== START MODALS ===============-->
<div id="modalEmailSave" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalEmailExport" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h4 id="modalEmailExport"> Email this Incident</h4>
                </div>
                <div class="modal-body">
                    <h5> Please enter an email address (multiple separated by commas):</h5>
                    {{ Form::text('incident_id',$incident->incident_id,array('class'=>'hide')) }}
                    {{ Form::text('email','',array('placeholder'=>'email@address.com','class'=>'form-control','id'=>'email')) }}
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
<!--=============== END MODALS ===============-->


<!--===============  START VISIBLE CONTENT ===============-->
@if(isset($incident))
{{ Form::model($incident, array('id'=>'editIncidentForm')) }} 

<div class='container'>
    <div class='row well whiteBackground'>
        <div class='col-md-8'>
            <p class="list-component">Incident Title: </p>
            <input type='text' data-bind='value: title' name='title' required maxlength='100' id='incidentTitle' class='form-control'/>
        </div>

        <div class='col-md-4'>
            <div class='pull-right'>
                <span>
                    <input href="#" type="submit" value="Save Edits" class="editIncidentButton btn btn-sm btn-success"/>
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
                    <a href='{{ URL::to("incident-cards/export",array($incident->incident_id)) }}'> 
                        <button class="btn btn-orange small" id="exportPDFButton" type='button'>
                            Export to PDF
                            <i class="glyphicon glyphicon-file"></i>
                        </button>
                    </a>
                </span> 
            </div>
        </div>
    </div> <!-- row -->

    <div class='row well whiteBackground'>
        <div class='col-md-2'>
            <div class="section-header-label"> Creation Date </div>
            <div class="content-description">
                <span title="{{ $incident->created_at }}"> 
                    {{ WKSSDate::display($incident->ts, $incident->created_at) }}
                </span>  
            </div>

            <div class='row'>
                <div class='col-md-12'>
                    <div class="section-header-label"> Project Management</div>
                    <div class="content-description">
                    </div>
                </div>
            </div>
        </div>

        <div class='col-md-2'>
            <div class="section-header-label"> Incident Types</div>
            <div class="details-content">
                <div class="content-description"> 
                    @foreach ($incident->incidentTypes as $t)
                        @if ($t->isMVD())
                            <a href ="#mvd_{{$t->incident_type_id}}" class="type-link">{{$t->type_name}}</a>
                        @elseif ($t->isMedical())
                            <a href ="#treatment_{{$t->incident_type_id}}" class="type-link">{{$t->type_name}}</a>
                        @elseif ($t->isReleaseAndSpill())
                            <a href ="#release_and_spill" class="type-link">{{$t->type_name}}</a>
                        @else
                            {{$t->type_name}}
                        @endif
                        <br/>
                    @endforeach
                </div>
                <br/>
            </div>
        </div>

        <div class='col-md-3'>
            <div class="section-header-label"> Activities </div>
            <div class="details-content">
                @if (count($incident->incidentActivities))
                    <div class="content-label">Currently Selected Activities </div>
                    <div class="content-description"> 
                        @foreach($incident->incidentActivities as $activity)
                            <li>{{ $activity->activity_name }}</li>
                        @endforeach
                    </div>
                @endif

                <?php
                    // Get associative array
                    $activities = Activity::lists('activity_name', 'activity_id');
                    // Sorts, but keeps IDs in tact
                    asort($activities);   
                ?>

                <div class="content-label">Modify Activities <i id="selectHelpButton" class="glyphicon glyphicon-question-sign"></i></div>
                <?php /*Here I used a hybrid between server side and client side --  server side to load the options and then client side to load what was selected*/?>
                {{Form::select('categoryIds[]', $activities, NULL , array('multiple'=>true,'class'=>'form-control', 'required', 'data-bind'=>'selectedOptions:incidentActivityIds', 'size'=>'7'))}}
            </div>
        </div>

        <div class='col-md-5'>
            <div class="section-header-label"> Photos </div>
            <br/>
            <div class='center'>
                @if ($incident->photos()->count())
                    <div class="nivoSlider photo-slider">
                        @foreach ($incident->photos as $photo) 
                            <a href="{{ Photo::generic($photo->name) }}" data-lightbox="incident">
                                <img src="{{ Photo::generic($photo->name) }}" />
                            </a>
                        @endforeach 
                    </div>
                @else
                    <div class="photo-slider">
                        <img class='' src="{{ URL::to('image/no-photo')}}" class=''>
                    </div>
                @endif
            </div>
        </div>

        <div class='row'>
            <div class='col-md-12'>
                <div class="section-header-label col-md-12"> Details </div>

                <div class='col-md-12'>
                    <div class="content-label">Description</div>
                    <div class="content-description">
                        <textarea class='wideTextBox form-control' data-bind='value: description' maxlength=1000 required></textarea>  
                    </div>
                </div>

                <div class='col-md-3'>
                    <div class="content-label">LSD</div>
                    <div class="content-description"> 
                        <input type='text' data-bind='value: lsd' class='form-control' maxlength=50 placeholder='###/##-##-###-##-W#M' title="###/##-##-###-##-W#M" pattern="\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM" /> 
                    </div>

                    <div class="content-label">Source Line and Receiver Line </div>
                    <div class="content-description"> 
                        <input type='text' class='form-control' data-bind='value: source_receiver_line' maxlength=100 /> 
                    </div>
                </div>

                <div class='col-md-3'>
                    <div class="content-label">Location of Incident</div>
                    <div class="content-description">
                        <textarea class='form-control' data-bind='value: location' maxlength=400 required></textarea> 
                    </div>

                    <div class="content-label">Specific Area of Incident</div>
                    <div class="content-description">
                        <textarea class='form-control' data-bind='value: specific_area' maxlength=400></textarea>  
                    </div>
                </div>

                <div class='col-md-3'>
                    <div class="content-label">Road or Intersection</div>
                    <div class="content-description">
                        <textarea class='form-control' data-bind='value: road' maxlength=400></textarea> 
                    </div>

                    <div class="content-label">Longitude</div>
                    <div class="content-description">
                        <input class='form-control' type='number' data-bind='value: longitude'></input>   
                    </div>

                    <div class="content-label">Latitude</div>
                    <div class="content-description">
                        <input class='form-control' type='number' data-bind='value: latitude'></input>  
                    </div>
                </div>

                <div class='col-md-3'>
                    <div class="content-label">Root Cause</div>
                    <div class="content-description">
                        <textarea class='form-control' data-bind='value: root_cause' maxlength=1000 required></textarea>  
                    </div>
                    <div class="content-label">Immediate Action Taken</div>
                    <div class="content-description">
                        <textarea class='form-control' data-bind='value: immediate_action' maxlength=1000 required></textarea>  
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div data-bind='if: releaseSpill' class="linkable" id="release_and_spill">
        <div class='well row whiteBackground'>
            <div class='row'><div class="section-header-label col-md-12"> Release and Spills </div></div>
            <div class='col-md-4'>
                <div class="content-label"> Commodity</div>
                <div class="content-description"><input type='text' class='form-control' data-bind='value: releaseSpill().commodity' maxLength='200' required></input></div>

                <div class="content-label"> Potential exposure to hazardous materials? </div>
                <input type='checkbox' data-bind='checkboxpicker: releaseSpill().potentialExposureToHazMat'></input>
                
                <div class="content-label"> General Comments</div>
                <div class="content-description"><textarea class='form-control' data-bind='value: releaseSpill().comment' rows=4 maxLength='1000'></textarea></div>
            </div>
                     
            <div class='col-md-4'>
                <div class="content-label"> Release source </div>
                <div class="content-description"><input type='text' class='form-control' data-bind='value: releaseSpill().release_source' maxLength='500'></input></div>
    
                <div class="content-label"> Quantity Released </div>
                <div class="content-description"><input type='number' class='form-control' data-bind='value: releaseSpill().quantity_released' max=9999999></input></div>

                <div class="content-label"> UOM </div>
                <div class="content-description">
                    <?php
                        $uomTypes = IncidentReleaseSpill::getUOMTypeList(); 
                    ?>

                    <?php /*Here I used a hybrid between server side and client side --  server side to load the options and then client side to load what was selected*/?>
                    {{Form::select('releaseUOMType', $uomTypes, NULL , array('class'=>'', 'required', 'data-bind'=>'value: releaseSpill().quantity_released_unit'))}}
                </div>  
            </div>

            <div class='col-md-4'>
                <div class="content-label"> Release to </div>
                <div class="content-description"><input type='text' class='form-control' data-bind='value: releaseSpill().release_to' maxLength='500'></input></div>

                <div class="content-label"> Quantity Recovered </div>
                <div class="content-description"><input type='number' class='form-control' data-bind='value: releaseSpill().quantity_recovered' max=9999999></input></div>

                <div class="content-label"> UOM</div>
                <div class="content-description">
                    <?php /*Here I used a hybrid between server side and client side --  server side to load the options and then client side to load what was selected*/?>
                    {{Form::select('recoveredUOMType', $uomTypes, NULL , array('class'=>'', 'required', 'data-bind'=>'value: releaseSpill().quantity_recovered_unit'))}}
                </div> 
            </div>
        </div>
    </div>
     
    <div data-bind='foreach: treatments'>
        <div class='well row whiteBackground' data-bind='attr: { id: "treatment_" + incident_type_id() }'>
            <div class='row'><div class="section-header-label col-md-12"> Medical Treatment (<span data-bind='text: $data.type().type_name'></span>) </div></div>
            <br/>
            <div class='row'>
                <div class='col-md-4'>
                    <div class="content-label"> First Aid</div>
                    <input type='checkbox' data-bind='checkboxpicker: firstAidWasGiven'></input>

                    <div class="content-label"> Medical Aid</div>
                    <input type='checkbox' data-bind='checkboxpicker: medicalAidWasGiven'></input>
                </div>

                <div class='col-md-4'>
                    <div class="content-label"> First Responder First &amp; Last Name</div>
                    <input type='text' class='form-control' data-bind='value: responder_name' maxLength='100' required></input>

                    <div class="content-label"> First Responder Company</div>
                    <input type='text' class='form-control' data-bind='value: responder_company' maxLength='500' required></input>

                    <div class="content-label"> First Responder Phone Number</div>
                    <input type='text' class='form-control' data-bind='value: responder_phone_number' pattern='\d\d\d-\d\d\d-\d\d\d\d' maxLength='50' required></input>
                </div>

                <div class='col-md-4'>
                    <div class="content-label"> General Comments</div>
                    <textarea class='form-control' data-bind='value: comment' maxLength='1000'></textarea>

                    <div class="content-label"> Bodily Injuries?</div>
                    <input type='checkbox' data-bind='checkboxpicker: thereWasBodilyInjury'></input>
                </div>


                <div data-bind='if: thereWasBodilyInjury' class='col-md-12'>
                    <button type="button" class="btn btn-md btn-primary" data-bind='click: addBodyPart'>Add Bodily Injury</button>
                    <br/><br/>
                    <div data-bind='foreach: parts'>
                        <div class='well col-md-4'>
                            <button type="button" class="btn btn-sm btn-warning pull-right" data-bind='click: $parent.removeBodyPart, visible: $parent.parts().length > 1'>Delete</button>
                            <div class="content-label"> Part</div>
                            <select class='form-control' data-bind='options: $parent.treatmentPartsList, optionsText: "description", optionsValue: "incident_schema_part_id", value: incident_schema_part_id'></select>
                            <div class="content-label"> Comment</div>
                            <textarea data-bind='value: comment' class='form-control' maxLength='1000'></textarea>
                            <div class="content-label"> Photos</div>
                            <div data-bind='foreach: photoIds'>
                                <a data-bind='attr: { href: photoURL(), "data-lightbox": "treatment_part_photos_" + $parentContext.$index() }'> <img data-bind='attr: { src: photoURL()}' style='width: 80px; height: 80px; border: solid 1px black; margin-top: 3px;'/></a>
                            </div>
                            <div data-bind='if: photoIds().length < 1'>
                                <ul><li>No Photos</li></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
     <div data-bind='foreach: mvds'>
        <div class='well row whiteBackground' data-bind='attr: {id: "mvd_" + incident_type_id()}'>
            <div class='row'><div class="section-header-label col-md-12"> Motor Vehicle Damage (<span data-bind='text: type().type_name'></span>)</div></div>
            <br/>
            <div class='row'>
                <div class='col-md-3'>
                    <div class="content-label"> Driver's License Number</div>
                    <input  type='text' class='form-control' data-bind='value: driver_license_number' maxLength='100' required></input>

                    <div class="content-label"> Insurance Company</div>
                    <input  type='text' class='form-control' data-bind='value: insurance_company' maxLength='500' required></input>

                    <div class="content-label"> Insurance Policy Number</div>
                    <input  type='text' class='form-control' data-bind='value: insurance_policy_number' maxLength='500' required></input>

                    <div class="content-label"> Policy Expiry Date</div>
                    <ul><li data-bind='text: policy_expiry_date'></li></ul>
                    <div class="container">
                        <div class='input-group date'>
                            <input class='form-control' type="text" data-bind='dateTimePicker: { format: "YYYY-MM-DD HH:mm:ss" }, value: policyExpiryDateTime' pattern='\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d' required></input> 
                        </div>
                    </div>

                    <div class="content-label"> Vehicle Year</div>
                    <input type='number' data-bind='value: vehicle_year' class='form-control' pattern='\d\d\d\d' title='####'></input>

                    <div class="content-label"> Vehicle Make</div>
                    <input type='text' class='form-control' data-bind='value: make' maxLength='100'></input>

                    <div class="content-label"> Vehicle Model</div>
                    <input type='text' class='form-control' data-bind='value: model' maxLength='300'></input>

                    <div class="content-label"> Vehicle Color</div>
                    <input type='text' class='form-control' data-bind='value: color' maxLength='50'></input>

                    <div class="content-label"> Vehicle VIN</div>
                    <input type='text' class='form-control' data-bind='value: vin' maxLength='100' required></input>

                    <div class="content-label"> Vehicle License Plate Number</div>
                    <input type='text' class='form-control' data-bind='value: license_plate' maxLength='100' required></input>
                    
                    <div class="content-label">Time of Incident</div>
                    <ul><li data-bind='text: time_of_incident'></li></ul>
                    <div class="container">
                        <div class='input-group date'>
                            <input type="text" class='form-control' data-bind='dateTimePicker: { format: "YYYY-MM-DD HH:mm:ss" }, value: timeOfMvdDateTime' pattern='\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d' required></input> 
                        </div>
                    </div> 
                </div> 

                <div class='col-md-3'> 
                    <div class="content-label"> Transportation of Dangerous Goods?</div>
                    <input type='checkbox' data-bind='checkboxpicker: thereIsTDG'></input>

                    <div data-bind='visible: thereIsTDG'>
                        <div class="content-label"> Dangerous Goods Material </div>
                        <input type='text' class='form-control' data-bind='value: tdg_material' maxLength='300'></input>
                    </div>

                    <div class="content-label"> Occupants wearing seatbelts?</div>
                    <input type='checkbox' data-bind='checkboxpicker: wearingSeatbelts'></input>

                    <div data-bind='if: !wearingSeatbelts()'>
                        <div class="content-label"> Why weren't occupants wearing seatbelts?</div>
                        <input type='text' class='form-control' data-bind='value: wearing_seatbelts_description' required maxLength='300'></input>
                    </div>

                    <div class="content-label"> Were the airbags deployed?</div>
                    <input type='checkbox' data-bind='checkboxpicker: airbags_deployed'></input>

                    <div class="content-label"> Was the vehicle towed? </div>
                    <input type='checkbox' data-bind='checkboxpicker: vehicleWasTowed'></input>

                    <div data-bind='if: vehicleWasTowed'>
                        <div class="content-label"> Towing Company Name</div>
                        <input type='text' class='form-control' data-bind='value: tow_company' maxLength='500'></input>
                        <div class="content-label"> Tow Driver Name</div>
                        <input type='text' class='form-control' data-bind='value: tow_driver_name' maxLength='100'></input>
                        <div class="content-label"> Tow Business Phone #</div>
                        <input type='text' class='form-control' data-bind='value: tow_business_phone_number' maxLength='50' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####'></input>
                        <div class="content-label"> Address Towed To</div>
                        <input type='text' class='form-control' data-bind='value: tow_address' maxLength='500'></input>
                    </div>
                </div>

                <div class='col-md-3'>
                    <div class="content-label"> Were other passengers involved?</div>
                    <input type='checkbox' data-bind='checkboxpicker: otherPassengersInvolved'></input>
                    <div data-bind='visible: otherPassengersInvolved'>
                        <div class="content-label"> Details about the other passengers</div>
                        <textarea data-bind='value: other_passengers_details' class='form-control' rows=4 maxLength='500'></textarea>
                    </div>

                    <div class="content-label"> Damage Exceeds 2000? </div>
                    <input type='checkbox' data-bind='checkboxpicker: damageExceedsAmount'></input>

                    <div data-bind='if: damageExceedsAmount'>
                        <div class="content-label"> Police File Number</div>
                        <input type='text' class='form-control' data-bind='value: police_file_number' maxLength='100'></input>

                        <div class="content-label"> Was there an attending Police Officer? </div>
                        <input type='checkbox' data-bind='checkboxpicker: thereWasAnAttendingPoliceOfficer'></input>

                        <div data-bind='if: thereWasAnAttendingPoliceOfficer'>
                            <div class="content-label"> Police Service Name</div>
                            <input type='text' class='form-control' data-bind='value: police_service' maxLength='200'></input>
                            <div class="content-label"> Officer First &amp; Last Name</div>
                            <input type='text' class='form-control' data-bind='value: police_name' maxLength='100'></input>
                            <div class="content-label"> Badge Number</div>
                            <input type='text' class='form-control' data-bind='value: police_badge_number' maxLength='100'></input>
                            <div class="content-label"> Business Phone Number</div>
                            <input type='text' class='form-control' data-bind='value: police_business_phone_number' maxLength='50' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####'></input>
                        </div>
                    </div>

                    <div class="content-label"> Number of other vehicles involved</div>
                    <input type='number' class='form-control' data-bind='value: vehicles_involved' min="0" max="99" step="1"></input>
                </div>

                <div class='col-md-3'>
                    <div data-bind='if: photos().length > 0'>
                        <div class='well'>
                            General MVD Photos
                            <div data-bind='foreach: photos'>
                                <a data-bind='attr: { href: photoURL(), "data-lightbox": "mvd_photos_" + $parentContext.$index() }'> <img data-bind='attr: { src: photoURL()}' style='width: 80px; height: 80px; border: solid 1px black; margin-top: 3px;'/></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-md-12'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <div class="content-label"> Was your vehicle damaged?</div>
                            <input type='checkbox' data-bind='checkboxpicker: vehicleWasDamaged'></input>
                        </div>

                        <div data-bind='if: vehicleWasDamaged' class='col-md-3'>
                            <div class="content-label"> General comments about the damaged vehicle</div>
                            <textarea  data-bind='value: comment' class='form-control' maxLength='1000'></textarea>
                        </div>

                        <div data-bind='if: vehicleWasDamaged' class='col-md-3'>
                            <div class="content-label"> Vehicle Type</div>
                            
                            <?php
                                // Get associative array
                                $vehicleTypes = IncidentMVD::getVehicleTypes(); 
                            ?>
                            <?php /*Here I used a hybrid between server side and client side --  server side to load the options and then client side to load what was selected*/?>
                            {{Form::select('vehicleType', $vehicleTypes, NULL , array('required', 'data-bind'=>'value:vehicleType', 'class'=>'form-control', 'disabled'))}}
                        </div>
                    </div>

                    <div data-bind='if: vehicleWasDamaged'>
                        <button type="button" class="btn btn-md btn-primary" data-bind='click: addVehiclePart'>Add Vehicle Damage</button>
                        <br/><br/>

                        <div data-bind='foreach: parts'>
                            <div class='well col-md-4'>
                                <button type="button" class="btn btn-sm btn-warning pull-right" data-bind='click: $parent.removeVehiclePart, visible: $parent.parts().length > 1'>Delete</button>
                                <div class="content-label"> Part</div>
                                <select class='form-control' data-bind='options: $parent.partsListForSelectedVehicleType, optionsText: "description", optionsValue: "incident_schema_part_id", value: incident_schema_part_id'></select>
                                <div class="content-label"> Comment</div>
                                <textarea data-bind='value: comment' class='form-control' maxLength='1000'></textarea>
                                <div class="content-label"> Photos</div>
                                <div data-bind='foreach: photoIds'>
                                    <a data-bind='attr: { href: photoURL(), "data-lightbox": "mvd_part_photos_" + $parentContext.$index() }'> <img data-bind='attr: { src: photoURL()}' style='width: 80px; height: 80px; border: solid 1px black; margin-top: 3px;'/></a>
                                </div>
                                <div data-bind='if: photoIds().length < 1'>
                                    <ul><li>No Photos</li></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- col md 12 -->

            </div> <!-- row -->
        </div> <!-- well -->
    </div> <!-- foreach -->

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class="section-header-label"> Workers </div>
            <br/>
            <button type="button" class="btn btn-md btn-primary" data-bind='click: addWorker'>Add Worker</button>
            <br/><br/>
            <div data-bind='foreach: persons'>
                <div data-bind='if: isWorker()'>
                    <div class='well col-md-3'>
                        <button type="button" class="btn btn-sm btn-warning pull-right" data-bind='visible: $parent.numWorkers() > 1, click: $parent.removeWorker'>Delete Worker</button>
                        <br/>
                        <div class="content-label"> First Name</div>
                        <input type='text' class='form-control' data-bind='value: first_name' maxLength=100 required/>
                        <div class="content-label"> Last Name</div>
                        <input type='text' class='form-control' data-bind='value: last_name' maxLength=100 required/>
                        <div class="content-label"> Company Name</div>
                        <input type='text' class='form-control' data-bind='value: company' maxLength=200 required/>
                        <div class="content-label"> Phone Number</div>
                        <input class='form-control' type='text' data-bind='value: phone_number' placeholder='###-###-####' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####' maxLength=50 required/>
                        <div class="content-label"> Employment Status</div>
                        <select class='form-control' data-bind='options: employmentTypes, optionsText: "label", optionsValue: "typeValue", value: employment_status' required></select>

                        <div class="content-label"> Time Came on Shift</div>
                        <ul><li data-bind='text: time_on_shift'></li></ul>
                        <div class="container">
                            <div class='input-group date'>
                                <input type="text" class='form-control' data-bind='dateTimePicker: { format: "YYYY-MM-DD HH:mm:ss" }, value: timeOnShiftDateTime' pattern='\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d' required='true'></input> 
                            </div>
                        </div>
        
                        <div class="content-label"> Actual Time of Incident</div>
                        <ul><li data-bind='text: time_of_incident'></li></ul>
                        <div class="container">
                            <div class='input-group date'>
                                <input type="text" class='form-control' data-bind='dateTimePicker: { format: "YYYY-MM-DD HH:mm:ss" }, value: timeOfIncidentDateTime' required='true' pattern='\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d'></input> 
                            </div>
                        </div>
                        <div class="content-label"> Worker Statement</div>
                        <textarea data-bind='value: statement' class='form-control' maxlength=1000 required rows=5></textarea>
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class="section-header-label"> Witnesses </div>
            <br/>
            Were there any witnesses? <br/>
            <input type='checkbox' data-bind='checkboxpicker: wereThereAnyWitnesses' id='witnessSlider' style='width:40px;'></input> 
            <button type="button" class="btn btn-md btn-primary" data-bind='click: addWitness'>Add Witness</button>
            <br/><br/>
            <div data-bind='foreach: persons'>
                <div data-bind='if: isWitness()'>
                    <div class='well col-md-3'>
                        <button type="button" class="btn btn-sm btn-warning pull-right" data-bind='visible: $parent.numWitnesses() > 1, click: $parent.removeWitness'>Delete Witness</button>
                        <br/>
                        <div class="content-label"> First Name</div>
                        <input type='text' data-bind='value: first_name' class='form-control' maxLength=100 required/>
                        <div class="content-label"> Last Name</div>
                        <input type='text' data-bind='value: last_name' class='form-control' maxLength=100 required/>
                        <div class="content-label"> Company Name</div>
                        <input type='text' data-bind='value: company' class='form-control' maxLength=200 required/>
                        <div class="content-label"> Phone Number</div>
                        <input type='text' data-bind='value: phone_number' class='form-control' placeholder='###-###-####' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####' maxLength=50 required/>
                        <div class="content-label"> Employment Status</div>
                        <select data-bind='options: witnessTypes, optionsText: "label", optionsValue: "typeValue", value: employment_status' class='form-control' required></select>
                        <div class="content-label"> Witness Statement</div>
                        <textarea data-bind='value: statement' class='form-control' maxlength=1000 required rows=5></textarea> 
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class="section-header-label"> 3rd Party </div>
            <br/>
            Were there any third parties involved? <br/>
        </div>
        <div class='col-md-12'>
            <input type='checkbox' data-bind='checkboxpicker: wereThereAnyThirdParties' id='thirdPartySlider' style='width:40px;'></input> 
            <button type="button" class="btn btn-md btn-primary" data-bind='click: addThirdParty'>Add 3rd Party</button>
            <br/><br/>
            <div data-bind='foreach: persons'>
                <div data-bind='if: isThirdParty()'>
                    <div class='well col-md-3'>
                        <button type="button" class="btn btn-xs btn-warning pull-right" data-bind='visible: $parent.numThirdParties() > 1, click: $parent.removeThirdParty'>Delete 3rd Party</button>
                        <br/>
                        <div class="content-label"> First Name</div>
                        <input type='text' data-bind='value: first_name' class='form-control' maxLength=100 required/>
                        <div class="content-label"> Last Name</div>
                        <input type='text' data-bind='value: last_name' class='form-control' maxLength=100 required/>
                        <div class="content-label"> Company Name</div>
                        <input type='text' data-bind='value: company' class='form-control' maxLength=200 required/>
                        <div class="content-label"> Phone Number</div>
                        <input type='text' data-bind='value: phone_number' class='form-control' placeholder='###-###-####' pattern='\d\d\d-\d\d\d-\d\d\d\d' title='###-###-####' maxLength=50 required/>
                        <div class="content-label"> Third Party Statement</div>
                        <textarea data-bind='value: statement' class='form-control' maxlength=1000 required rows=5></textarea> 
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class="section-header-label"> Corrective Action</div>
        </div>
        <div class='col-md-4'>
            <div class="content-label"> Corrective action </div>
            <textarea data-bind='value: corrective_action' class='form-control' maxlength=1000 required></textarea> 
        </div>

        <div class='col-md-4'>
            <div class="content-label">Was the corrective action applied? </div>
            <input type='checkbox' id='correctiveActionAppliedSlider' data-bind='checkboxpicker: correctiveActionWasApplied'></input>
            
            <div class="content-label"> Reason corrective action was not immediately applied</div>
            <textarea data-bind='value: corrective_action_implementation' class='form-control' maxLength='1000'></textarea>
        </div>

        <div class='col-md-4'>
            <div class="required-action-item"> 
                <form class="action-form" id="{{ $incident->incident_id }}">
                    <div class="content-label"> Completed On </div>
                    <div class="containerDate">
                        <input type="text" class='form-control' data-bind='dateTimePicker: { format: "YYYY-MM-DD" }, value: completed_on' pattern='\d\d\d\d-\d\d-\d\d'></input> 
                    </div>
                
                    <div class="content-label"> Required Action </div>
                    <textarea data-bind='value: action' class='form-control' class='action-description' maxLength='500' rows=4 placeholder='Describe the required action for this item'></textarea>
                </form>
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-6'>
            <div class="section-header-label"> Performed by </div>
            <div class="details-content">
                <div class="content-label"> Worker name  </div>
                <div class="content-description"> {{ $incident->addedBy->first_name.' '.$incident->addedBy->last_name }} </div>
            </div>
            <div class="details-content">
                <div class="content-label"> Signature  </div>
                @if ($incident->addedBy->signature())
                <div class="content-description center"> 
                    <img src="{{ URL::to('image/worker/signature',$incident->addedBy->auth_token) }} " class="signature-image">
                </div>
                @else
                <div class="content-description"> 
                    No signature available
                </div>
                @endif
            </div>
        </div>

        <div class='col-md-6'>
            <div class="section-header-label"> Review by management</div>
            
            @if (!$incident->review)
                <form id="review-details" class="hidden">
                    <input type="text" name="resource_type" value="{{ get_class($incident) }}"/>
                    <input type="text" name="resource_id" value="{{ $incident->incident_id}}"/>
                </form>
                <div class="content-label" style="padding-right:5px;">
                    <button type='button' class="btn btn-info" id="review-button"> <b>Review this form as {{ Auth::user()->first_name.' '.Auth::user()->last_name }} </b></button>
                </div>
            @else
                <div class="details-content">
                    <div class="content-label"> Reviewed by: 
                        <span class="content-description"> {{ $incident->review->reviewer_name }} </span>
                        <br/>
                        On:
                        <span class="content-description"> {{ WKSSDate::display($incident->review->ts,$incident->review->created_at) }} </span>
                    </div>
                </div>
                <div class="details-content">
                    <div class="content-label"> Signature  </div>
                    @if ($incident->review->signature())
                        <div class="content-description center"> 
                            <img src="{{ URL::to('image/review/signature',$incident->review->form_review_id ) }}" class="admin-signature-image">
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

</div> <!-- container-fluid-->


{{ Form::close() }}
@endif
@stop

@section('scripts')
@parent 
<script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
<script src="{{{ asset('assets/js/modernizr.custom.js') }}}"></script>
<script src="{{{ asset('assets/js/jquery.nivo.slider.js') }}}"></script>
<script src="{{{ asset('assets/js/wkss/incident-card-details.js') }}}"></script>
<script src="{{{ asset('assets/js/bootstrap-checkbox.min.js') }}}" ></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
<script type="text/javascript" src="https://cdn.rawgit.com/Eonasdan/bootstrap-datetimepicker/d004434a5ff76e7b97c8b07c01f34ca69e635d97/src/js/bootstrap-datetimepicker.js"></script>
<script type="text/javascript">
    <?php
        // Computations for JavaScript variables
        // Eventually the ideal approach would be to generate these values from somewhere else get get them
        // via a JSON call

        // Constants
        define('TREATMENT_TYPE', 1);
        define('TRAILER_TYPE', 2);
        define('TRUCK_TYPE', 3);

        $chosenActivityIds = array();
        foreach ($incident->incidentActivities as $chosenActivity) {
            array_push($chosenActivityIds, $chosenActivity->activity_id);
        }

        // We are getting these here because for some reason this data is not included with an MVD regularly
        // It will be handed to knockout which will then assign the photos to the correct MVD
        $mvdPhotos = array();
        $count = 0;
        foreach ($incident->mvds as $mvd) {
            $mvdPhotos[$count]['incident_mvd_id'] = $mvd->incident_mvd_id;
            $mvdPhotos[$count]['photoIds'] = $mvd->extractPhotoIds();
            $count++;
        }

        // This code is necessary to extract more info from the incident types so that when they are printed into variables, all of the
        // information is printed.
            foreach ($incident->treatments as $treatment) {
                $incident->treatment = $treatment; 
                $treatment = $incident->treatment->getWithDetails();
                $incident->treatment->type->type_name;
            } 

            foreach ($incident->mvds as $mvd){
                $incident->mvd = $mvd;
                $incident->mvd->type->type_name;
                $incident->mvd->extractPhotoIds();
            }
    ?>

    // Declarations which will be used for Knockout.JS
    var theIncident =  {{ json_encode($incident) }} ;
    var chosenActivityIds = {{ json_encode($chosenActivityIds) }};
    var persons = {{ json_encode($incident->persons) }};
    var mvds = {{ json_encode($incident->mvds) }};
    var mvdPhotos = {{ json_encode($mvdPhotos) }};
    var treatments = {{ json_encode($incident->treatments) }};
    var releaseSpill = {{ json_encode($incident->releaseSpill) }};
    
    var treatmentPartsList = {{ json_encode(IncidentSchemaPart::where('incident_schema_id','=', TREATMENT_TYPE)->get()) }};
    var truckPartsList =     {{ json_encode(IncidentSchemaPart::where('incident_schema_id','=', TRUCK_TYPE)->get()) }};
    var trailerPartsList =   {{ json_encode(IncidentSchemaPart::where('incident_schema_id','=', TRAILER_TYPE)->get()) }};
</script>
@stop
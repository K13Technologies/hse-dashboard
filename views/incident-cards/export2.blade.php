@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <style>
        .navbar{
            display:none;
        }
        .row-fluid{
            margin-left:-10px!important;
        }
        .span3:first-child{
            padding-left:10px;
        }
        table{
            width:100%;
            
        }
        table>thead>tr>th{
            padding:0;
        }
        table>tbody>tr>td{
            padding:10px;
            vertical-align: top;
        }
        .list-container{
            page-break-inside:avoid;
        }
        .list-container-header.bordered{
            border-top: 1px solid #d7e3f0;
        }
        tbody:before, tbody:after { 
            /*BOOTSTRAP OVERRIDE TO ALLOW FOR PROPER TABLE FORMATTING*/
            display: none !important; 
        }
    </style>
@stop

@section('content')
    @if ($incident->addedBy->company->logo())
        <img src="{{Photo::generic($incident->addedBy->company->logo()->name)}}" id='companyLogo'/> 
    @endif
    <div style="text-align:center">
       <h2> {{ $incident->addedBy->company->company_name }} </h2>
       <h4> Incident Card </h4>
    </div>
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component">{{ $incident->title }} </span>
                <span class="right-header-text"> ({{ $incident->created_at }})  </span>
            </div>
        </div>
    </div>
    <table>
        <tbody>
            <tr>
                <td colspan="2">
                    <div class="section-header-label"> Details </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">LSD</div>
                </td>
                <td>
                    <div class="content-description"> {{ $incident->lsd }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">UTM</div>
                </td>
                <td>
                    <div class="content-description"> {{ $incident->utm }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Source line and receiver line </div>
                </td>
                <td>
                    <div class="content-description"> {{ $incident->source_receiver_line }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Location of incident</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->location }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Specific area of incident</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->specific_area }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Road or intersection</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->road }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Description</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->description }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Root cause</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->root_cause }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Immediate action taken</div>
                </td>
                <td>
                    <div class="content-description">{{ $incident->immediate_action }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                        <div class="section-header-label"> ACTIVITIES </div>
                        <div class="content-description"> 
                            <br/>
                            @if (count($incident->incidentActivities))
                                {{ implode(', ',array_pluck($incident->incidentActivities,'activity_name')) }}
                            @endif
                        </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="section-header-label"> INCIDENT TYPES</div>
                    <div class="content-description"> 
                        <br/>
                        @if (count($incident->incidentTypes))
                            {{ implode(', ',array_pluck($incident->incidentTypes,'type_name')) }}
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="section-header-label"> CORRECTIVE ACTION</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label"> Required corrective action</div>
                </td>
                <td>
                    <div class="content-description"> {{ $incident->corrective_action }} </div>
                </td>
            </tr>
            @if ($incident->corrective_action_applied)
                <tr>
                    <td>
                        <div class="content-label"> Applied corrective action</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $incident->corrective_action_implementation }} </div>
                    </td>
                </tr>
            @else
                <tr>
                    <td>
                        <div class="content-label"> Corrective action not applied</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $incident->corrective_action_implementation }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Required action set by admin </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $incident->action?$incident->action:"N/A" }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Status </div>
                    </td>
                    <td>
                        <div class="content-description"> 
                            @if ($incident->completed_on != NULL)
                                    Completed on <b> {{ $incident->completed_on }}</b>
                            @else
                                    <b> Outstanding </b> 
                            @endif
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($incident->persons()->count())
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component"> WORKERS  </span>
                </div>
            </div>
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="height:45px;"> Worker Name </th>
                        <th style="height:45px;"> Company </th>
                        <th style="height:45px;"> Phone Number </th>
                        <th style="height:45px;"> Employment status </th>
                        <th style="height:45px;"> Time on shift </th>
                        <th style="height:45px;"> Time of incident </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ( $incident->persons as $p )
                        @if ($p->type == IncidentPerson::TYPE_WORKER)
                            <tr>
                                <td>
                                    {{ $p->first_name.' '.$p->last_name }} 
                                </td>
                                <td>
                                    {{ $p->company }}
                                </td>
                                <td>
                                    {{ $p->phone_number }}
                                </td>
                                <td>
                                    {{ IncidentPerson::$employeeStatuses[$p->employment_status] }}
                                </td>
                                <td>
                                    {{ $p->time_on_shift }}
                                </td>
                                <td>
                                    {{ $p->time_of_incident}}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <br/>
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component"> WITNESSES  </span>
                </div>
            </div>
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="height:45px;"> Witness Name </th>
                        <th style="height:45px;"> Company </th>
                        <th style="height:45px;"> Phone Number </th>
                        <th style="height:45px;"> Employment status </th>
                        <th style="height:45px;"> Statement </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ( $incident->persons as $p )
                        @if ($p->type == IncidentPerson::TYPE_WITNESS)
                            <tr>
                                <td class="center">
                                    {{ $p->first_name.' '.$p->last_name }} 
                                </td>
                                <td class="center">
                                    {{ $p->company }}
                                </td>
                                <td class="center">
                                    {{ $p->phone_number }}
                                </td>
                                <td class="center">
                                    {{ IncidentPerson::$employeeStatuses[$p->employment_status] }}
                                </td>
                                <td class="center">
                                    {{ $p->statement}}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <br/>
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component"> THIRD PARTIES  </span>
                </div>
            </div>
            <table class="bordered" style='width:100%;'>
                <thead>
                    <tr>
                        <th style="height:45px;"> Third Party Name </th>
                        <th style="height:45px;"> Company </th>
                        <th style="height:45px;"> Phone Number </th>
                        <th style="height:45px;"> Statement </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ( $incident->persons as $p )
                        @if ($p->type == IncidentPerson::TYPE_3RD_PARTY)
                            <tr>
                                <td class="center">
                                    {{ $p->first_name.' '.$p->last_name }} 
                                </td>
                                <td class="center">
                                    {{ $p->company }}
                                </td>
                                <td class="center">
                                    {{ $p->phone_number }}
                                </td>
                                <td class="center">
                                    {{ $p->statement}}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif



     @foreach ($incident->mvds as $mvd)
        <?php $incident->mvd = $mvd ?>
        <br/>
        <div class="list-container-header bordered">
            <div class="list-component-container">
                <span class="list-component">Motor Vehicle Damage</span>
                <span class="list-component">({{ $incident->mvd->type->type_name }})</span>
            </div>
        </div>
            <table class="incident-details-table bordered">
                            <tr>
                                <td>
                                    <div class="content-label"> Driver's License number </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->driver_license_number }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Insurance Company </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->insurance_company }} </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="content-label"> Insurance Policy Number </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->insurance_policy_number }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Policy Expiry Date</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->policy_expiry_date }} </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="content-label"> Vehicle Make</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->make }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> VIN #</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->vin }} </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="content-label"> Vehicle Model (Vehicle Year) </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->model }} ({{ $incident->mvd->vehicle_year }}) </div>
                                </td>
                                <td>
                                    <div class="content-label"> Vehicle Color</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->color }} </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="content-label"> Time of incident </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ WKSSDate::display($incident->mvd->ts_of_incident,$incident->mvd->time_of_incident) }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> TDG </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ ($incident->mvd->tdg)?"Yes":"No" }} </div>
                                </td>
                            </tr>
                            @if ($incident->mvd->tdg)
                            <tr>
                                <td colspan="2">
                                    <div class="content-label"> DG Material</div>
                                </td>
                                <td colspan="2">
                                    <div class="content-description"> {{ $incident->mvd->tdg_material }} </div>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>
                                    <div class="content-label"> Occupants wearing seatbelts? </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ ($incident->mvd->wearing_seatbelts)?"Yes":"No" }} </div>
                                </td>
                            @if (!$incident->mvd->wearing_seatbelts)
                                <td>
                                    <div class="content-label"> Occupants wearing seatbelts description </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->wearing_seatbelts_description }} </div>
                                </td>
                            </tr>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                            <tr>
                                <td>
                                    <div class="content-label"> Were the airbags deployed?</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ ($incident->mvd->airbags_deployed)?"Yes":"No" }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Was your vehicle towed?</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ ($incident->mvd->vehicle_towed)?"Yes":"No" }} </div>
                                </td>
                            </tr>
                            @if($incident->mvd->vehicle_towed)
                            <tr>
                                <td>
                                    <div class="content-label"> Tow Company</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->tow_company }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Tow Driver Name</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->tow_driver_name }} </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="content-label"> Tow Business Phone #</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->tow_business_phone_number }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Address Towed To </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->tow_address }} </div>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td>
                                    <div class="content-label"> Other passengers involved </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->other_passengers?"Yes":"No" }} </div>
                                </td>
                            @if ($incident->mvd->other_passengers)
                                <td>
                                    <div class="content-label"> Other passengers </div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->other_passengers_details }} </div>
                                </td>
                            </tr>
                            @else
                                <td></td>
                                <td></td>
                            @endif
                            <tr>
                                <td colspan="2">
                                    <div class="content-label"> Damage Exceeds $2000? </div>
                                </td>
                                <td colspan="2">
                                    <div class="content-description"> {{ $incident->mvd->damage_exceeds_amount?"Yes":"No" }} </div>
                                </td>
                            </tr>
                            @if($incident->mvd->damage_exceeds_amount) 
                                <tr>
                                    <td>
                                        <div class="content-label"> Police File Number</div>
                                    </td>
                                    <td>
                                        <div class="content-description"> {{ $incident->mvd->police_file_number }} </div>
                                    </td>
                                    <td>
                                        <div class="content-label"> Attending Police Officer </div>
                                    </td>
                                    <td>
                                        <div class="content-description"> {{ $incident->mvd->attending_police_officer?"Yes":"No" }} </div>
                                    </td>
                                </tr>
                                @if ($incident->mvd->attending_police_officer)
                                    <tr>
                                        <td>
                                            <div class="content-label"> Police Service</div>
                                        </td>
                                        <td>
                                            <div class="content-description"> {{ $incident->mvd->police_service }} </div>
                                        </td>
                                        <td>
                                            <div class="content-label"> Officer Name</div>
                                        </td>
                                        <td>
                                            <div class="content-description"> {{ $incident->mvd->police_name }} </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="content-label"> Officer Badge #</div>
                                        </td>
                                        <td>
                                            <div class="content-description"> {{ $incident->mvd->police_badge_number }} </div>
                                        </td>
                                        <td>
                                            <div class="content-label"> Police Officer Business Phone #</div>
                                        </td>
                                        <td>
                                            <div class="content-description"> {{ $incident->mvd->police_business_phone_number }} </div>
                                        </td>
                                    </tr>
                                @endif
                            @endif
                            <tr>
                                <td>
                                    <div class="content-label"> Number of other vehicles involved</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->vehicles_involved }} </div>
                                </td>
                                <td>
                                    <div class="content-label"> Was your vehicle damaged?</div>
                                </td>
                                <td>
                                    <div class="content-description"> {{ $incident->mvd->my_vehicle_damaged?"Yes":"No" }} </div>
                                </td>
                            </tr>
                            @if ($incident->mvd->my_vehicle_damaged)
                            <tr>
                                <td colspan="2">
                                    <div class="content-label"> Vehicle Type</div>
                                </td>
                                <td colspan="2">
                                    <div class="content-description"> {{ IncidentMVD::$vehicle_types[$incident->mvd->vehicleType] }} </div>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="2"> <div class="content-label"> Comment </div> </td>
                                <td colspan="2"> <div class="content-description"> {{ $mvd->comment }} </div> </td>
                            </tr>
                            </table>
            <?php $mvd = $incident->mvd->getWithDetails() ?>
            <br/>
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="text-align: left;padding-left:10px"> <b> Part <b> </th>
                        <th style="text-align: left;padding-left:10px"> <b> Comment </b> </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mvd->parts as $s)
                    <tr>
                        <td>
                            <div class="content-label"> {{ ucwords(str_replace('_',' ', IncidentSchemaPart::find($s['incident_schema_part_id'])->key)) }}</div>
                        </td>
                        <td>
                            <div class="content-label"> {{ $s['comment'] }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @foreach ($mvd->parts as $p) 
                @if (count($p['photoIds']))
                    <div> {{ IncidentSchemaPart::find($p['incident_schema_part_id'])->description }}  </div>
                    <div>
                        @foreach ($p['photoIds'] as $photo)
                            <img src="{{ Photo::generic($photo) }}" class="pdf-image"/>
                        @endforeach
                    </div>
                @endif
            @endforeach 
    @endforeach

    @foreach ($incident->treatments as $treatment)
        <?php $incident->treatment = $treatment ?>
        <br/>
        <div class="list-container-header bordered">
            <div class="list-component-container">
                <span class="list-component">Medical Treatment</span>
                <span class="list-component">({{ $incident->treatment->type->type_name }})</span>
            </div>
        </div>
        <div class="list-container-body" >
            <table class="incident-details-table bordered">
                 <tr>
                    <td>
                            <div class="content-label"> First Aid</div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->treatment->first_aid?"Yes":"No"}} </div>
                        </td>
                        <td>
                            <div class="content-label"> Medical Aid </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->treatment->medical_aid?"Yes":"No"}} </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> First responder name </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->treatment->responder_name }} </div>
                        </td>
                        <td>
                            <div class="content-label"> Company </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->treatment->responder_company }} </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> Phone Number</div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->treatment->responder_phone_number }} </div>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    
            </table>
            <?php $treatment = $incident->treatment->getWithDetails() ?>
            <br/>
            <table class="bordered">
                <thead>
                    <tr>
                        <th style="text-align: left;padding-left:10px"> <b> Part <b> </th>
                        <th style="text-align: left;padding-left:10px"> <b> Comment </b> </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($treatment->parts as $s)
                    <tr>
                        <td>
                            <div class="content-label"> {{ ucwords(str_replace('_',' ', IncidentSchemaPart::find($s['incident_schema_part_id'])->key)) }}</div>
                        </td>
                        <td>
                            <div class="content-label"> {{ $s['comment'] }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <?php $treatment->getWithDetails() ?>
            
            @foreach ($treatment->parts as $p) 
                @if (count($p['photoIds'])) 
                    <div> {{ IncidentSchemaPart::find($p['incident_schema_part_id'])->description }} </div>
                    <div>
                        @foreach ($p['photoIds'] as $photo)
                            <img src="{{ Photo::generic($photo) }}" class="pdf-image"/>
                        @endforeach
                    </div>
                @endif
            @endforeach 
        </div>
    @endforeach

    @if ($incident->hasReleaseSpill())
        <br/>
        <div class="list-container-header bordered">
            <div class="list-component-container">
                <span class="list-component">Release and Spills</span>
            </div>
        </div>
        <div class="list-container-body" >
            <table class="incident-details-table bordered">
                <tr>
                    <td>
                            <div class="content-label"> Commodity</div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->commodity }} </div>
                        </td>
                        <td>
                            <div class="content-label"> Potential exposure to hazardous materials? </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->potential_exposure?"Yes":"No" }} </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> Release source </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->release_source }} </div>
                        </td>
                        <td>
                            <div class="content-label"> Release to </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->release_to }} </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> Quantity released </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->quantity_released." ".$incident->releaseSpill->quantity_released_unit }} </div>
                        </td>
                        <td>
                            <div class="content-label"> Quantity recovered </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $incident->releaseSpill->quantity_recovered." ".$incident->releaseSpill->quantity_recovered_unit }} </div>
                        </td>
                    </tr>
                    <tr>
                    <td colspan="1">
                            <div class="content-label"> Comment</div>
                        </td>
                        <td colspan="3">
                            <div class="content-description"> {{ $incident->releaseSpill->comment }} </div>
                        </td>
                    </tr>
            </table>
            </div>

    @endif

    @if($incident->photos()->count())
        <div class="list-container-header bordered">
            <div class="list-component-container">
                <span class="list-component">Incident photos</span>
            </div>
        </div>  
        <div id="pdf-image-container">
            @foreach ($incident->photos as $photo) 
                <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
            @endforeach 
        </div>
    @endif

    <div class="list-container">
                <div class="list-container-header">
                  <div class="list-component-container">
                      <span class="list-component"> Signatures </span>
                  </div>
                </div>
                <table>
                    <tbody>
                    <tr>
                        <td colspan="2">
                              <div class="section-header-label"> Performed by </div>
                        </td>
                        <td colspan="2">
                            <div class="section-header-label"> Review by management</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> Worker name  </div>
                            <div class="content-description"> {{ $incident->addedBy->first_name.' '.$incident->addedBy->last_name }} </div>
                        </td>
                        <td>
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
                       </td>
                       @if ($incident->review)
                       <td> 
                            <div class="details-content">
                                    <div class="content-label"> Reviewed by: 
                                        <span class="content-description"> {{ $incident->review->reviewer_name }} </span>
                                        <br/>
                                        On:
                                        <span class="content-description"> {{ WKSSDate::display($incident->review->ts,$incident->review->created_at) }} </span>
                                    </div>
                                </div>
                            </td>
                            <td>
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
                            </div>
                            </td>
                        @else
                            <td></td>
                            <td></td>
                        @endif
                    </tr>
                </tbody>
            </table>
    </div>
@stop
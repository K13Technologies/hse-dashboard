@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/wkss/vehicle-management/vehicle-details.css') }}}" rel="stylesheet">
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
        .high-risk{
            color: #b94a48;
        }
        .medium-risk{
            color:#c09853;
        }
        .low-risk{
            color:#468847;
        }
        .modal-content-description{
            padding: 5px;
            color: #505760;
            font-weight: bold;
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
    </style>
@stop

@section('content')
@if ($flha->addedBy->company->logo())
    <img src="{{Photo::generic($flha->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $flha->addedBy->company->company_name }} </h2>
   <h4> FLHA Card </h4>         
</div>
   <div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">{{ $flha->title }}</span>
                  <span class="right-header-text"> ({{ $flha->created_at }})  </span>
              </div>
            </div>
            <div class="list-container-body" id="showForm">
                    <table>
                        <tr>
                            <td>
                                <div class="section-header">
                                    <div class="section-header-label"> FLHA DETAILS </div>
                                    <div class="details-content">
                                        <div class="content-label">Location</div>
                                        <div class="content-description"> {{ $flha->location }} </div>
                                        <div class="content-label">Site</div>
                                        <div class="content-description"> {{ $flha->site }} </div>
                                        <div class="content-label">Specific location</div>
                                        <div class="content-description"> {{ $flha->specific_location }} </div>
                                        <div class="content-label">Client</div>
                                        <div class="content-description"> {{ $flha->client }} </div>
                                        <div class="content-label">Muster Point</div>
                                        <div class="content-description"> {{ $flha->muster_point }}</div>
                                        <div class="content-label">Supervisor Number</div>
                                        <div class="content-description"> {{ $flha->supervisor_number }}</div>
                                        <div class="content-label">Radio Channel</div>
                                        <div class="content-description"> {{ $flha->radio_channel }}</div>
                                        <div class="content-label">Supervisor Name</div>
                                        <div class="content-description"> {{ $flha->supervisor_name }}</div>
                                    </div>
                                </div>  
                            </td>
                            <td>
                                    <div class="section-header">
                                            <div class="section-header-label"> JOB DESCRIPTION </div>
                                            <div class="details-content">
                                                <div class="content-description"> {{ $flha->job_description }} </div>
                                            </div>
                                    </div>
                                        <div class="section-header">
                                            <div class="section-header-label"> SAFETY QUESTIONS </div>
                                            <div class="details-content">
                                                <div class="content-label">Do you need to remove your gloves ?</div>
                                                <div class="content-description"> 
                                                    @if ($flha->gloves_removed)
                                                        {{ $flha->gloves_removed_description }}
                                                    @else
                                                        NO
                                                    @endif
                                                </div>
                                                <div class="content-label">Are you working alone?</div>
                                                <div class="content-description"> 
                                                    @if ($flha->working_alone)
                                                        {{ $flha->working_alone_description }}
                                                    @else
                                                        NO
                                                    @endif
                                                </div>
                                                <div class="content-label">Warning ribbon needed?</div>
                                                <div class="content-description"> 
                                                    @if ($flha->warning_ribbon)
                                                        {{ $flha->warning_ribbon_description }}
                                                    @else
                                                        NO
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="section-header">
                                            <div class="section-header-label"> TASK OBSERVED </div>
                                            <div class="details-content">
                                                <?php 
                                                $risklevel = array('low-risk','medium-risk','high-risk');
                                                ?>
                                                
                                                @foreach ($flha->tasks as $t)
                                                    <div style="font-size:16px;font-weight:bold;padding-top:5px" class="text-success"> {{ $t->title }} </div>
                                                    @foreach ($t->hazards as $h)
                                                    <div style="border-top:1px solid #ccc">
                                                            <div class="content-label"> Hazard Details</div>
                                                            <div class="content-description {{ $risklevel[$h->risk_level] }}">
                                                                    {{ $h->description }} 
                                                            </div>
                                                            <div class="content-label"> Estimated/Control Hazard</div>
                                                            <div class="content-description {{ $risklevel[$h->risk_assessment] }}">
                                                                    {{ $h->eliminate_hazard }} 
                                                            </div> 
                                                    </div>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>  
                           </td>
                            <td>
                                    <div class="section-header">
                                            <div class="section-header-label"> HAZARD CHECKLIST </div>
                                            <div class="details-content">
                                                @foreach ($flha->checklist as $categoryId => $hazardList )
                                                    <div class="content-label"> {{ HazardChecklistCategory::find($categoryId)->category_name }} </div>
                                                        <div class="content-description">
                                                               @foreach ( $hazardList as $h )
                                                                <div> {{ $h->item_name }} </div>
                                                               @endforeach
                                                        </div>
                                                @endforeach
                                            </div>
                                        </div>  
                                       <div class="section-header">
                                            <div class="section-header-label"> VISITORS </div>
                                            <div class="details-content">
                                                <div class="content-description"> 
                                                    @foreach ( $flha->signoffVisitors as $v )
                                                        <div> {{ $v->first_name.' '.$v->last_name }}  </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>  
                                       <div class="section-header">
                                            <div class="section-header-label"> WORKERS </div>
                                            <div class="details-content">
                                                <div class="content-description"> 
                                                    @foreach ( $flha->signoffWorkers as $w )
                                                        <div> {{ $w->first_name.' '.$w->last_name }} </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>  
                            </td>
                            <td>
                                        <div class="section-header">
                                        <div class="section-header-label"> SPOTCHECKS </div>
                                           <div class="details-content">
                                               <div class="content-description"> 
                                                   @foreach ( $flha->spotchecks as $s )
                                                       <div> {{ $s->first_name.' '.$s->last_name }} </div>
                                                   @endforeach
                                               </div>
                                           </div>
                                       </div> 
                                       @if ($flha->completion instanceOf JobCompletion)
                                           <div class="section-header">
                                               <div class="section-header-label"> JOB COMPLETION </div>
                                               <div class="details-content">
                                                   <div class="content-label"> Permit closed? </div>
                                                   <div class="content-description"> {{ $flha->completion->permit_closed?"YES":$flha->completion->permit_closed_description }} </div>
                                                   <div class="content-label"> Hazard remaining? </div>
                                                   <div class="content-description"> {{ $flha->completion->hazard_remaining?$flha->completion->hazard_remaining_description:"NO" }} </div>
                                                   <div class="content-label"> Flagging removed? </div>
                                                   <div class="content-description"> {{ $flha->completion->flagging_removed?"YES":$flha->completion->flagging_removed_description }} </div>
                                                   <div class="content-label"> All incidents/injuries reported? </div>
                                                   <div class="content-description"> {{ $flha->completion->incident_reported?"YES":$flha->completion->incident_reported_description }} </div>
                                                   <div class="content-label"> Concerns addressed and documented? </div>
                                                   <div class="content-description"> {{ $flha->completion->concerns?"YES":$flha->completion->concerns_description }} </div>
                                                   <div class="content-label"> Tools/equipment removed? </div>
                                                   <div class="content-description"> {{ $flha->completion->equipment_removed?"YES":$flha->completion->equipment_removed_description }} </div>
                                               </div>
                                           </div> 
                                       @endif

                                <div class="section-header">
                                  <div class="section-header-label"> Performed by </div>
                                    <div class="details-content">
                                        <div class="content-label"> Worker name  </div>
                                        <div class="content-description"> {{ $flha->addedBy->first_name.' '.$flha->addedBy->last_name }} </div>
                                    </div>
                                   <div class="details-content">
                                        <div class="content-label"> Signature  </div>
                                        @if ($flha->addedBy->signature())
                                            <div class="content-description center"> 
                                                <img src="{{ URL::to('image/worker/signature',$flha->addedBy->auth_token) }} " class="signature-image">
                                            </div>
                                        @else
                                            <div class="content-description"> 
                                                No signature available
                                            </div>
                                        @endif
                                    </div>
                                </div> 
                            </td>
                        </tr>
                    </table>
            </div>
        </div>        
    @if($flha->photos()->count())
        <div id="pdf-image-container">
           @foreach ($flha->photos as $photo) 
                <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
           @endforeach 
        </div>
    @endif
@stop
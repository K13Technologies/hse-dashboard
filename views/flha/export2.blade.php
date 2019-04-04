@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
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
        td.text-success{
            font-weight: bold;
            border:1px solid #ccc;
        }
        .low-risk,.medium-risk,.high-risk{
            font-weight: normal;
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
</div>
        <table>
            <tr>
                <td colspan="2">
                    <div class="section-header-label"> FLHA DETAILS</div>
                </td>
            </tr>
            <tr>
                <td>
                        <div class="content-label">Location</div>
                </td>
                <td>
                        <div class="content-description"> 
                            <?php $locations = array_pluck($flha->locations()->get()->all(),'location');
                                if ($flha->location){
                                    $locations[] = $flha->location; 
                                } ?>
                                {{ implode(", ",$locations) }}
                        </div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Site</div>
                </td>
                <td>
                            <div class="content-description"> 
                                <?php $sites = array_pluck($flha->sites()->get()->all(),'site');
                                    if ($flha->location){
                                        $sites[] = $flha->site; 
                                    } ?>
                                    {{ implode(", ",$sites) }}
                            </div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">LSD</div>
                </td>
                <td>
                            <div class="content-description"> 
                                <?php $lsds = array_pluck($flha->lsds()->get()->all(),'lsd');
                                        if ($flha->specific_location){
                                              $lsds[] = $flha->specific_location; 
                                        } ?>
                                        {{ implode(", ",$lsds) }}
                            </div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Permits</div>
                </td>
                <td>
                            <div class="content-description"> 
                                {{ implode(", ",array_pluck($flha->permits()->get()->all(),'permit_number')) }}
                            </div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Client</div>
                </td>
                <td>
                            <div class="content-description"> {{ $flha->client }} </div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Muster Point</div>
                </td>
                <td>
                            <div class="content-description"> {{ $flha->muster_point }}</div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Supervisor Number</div>
                </td>
                <td>
                            <div class="content-description"> {{ $flha->supervisor_number }}</div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Radio Channel</div>
                </td>
                <td>
                            <div class="content-description"> {{ $flha->radio_channel }}</div>
                </td>
            </tr>
            <tr>
                <td>
                            <div class="content-label">Supervisor Name</div>
                </td>
                <td>
                            <div class="content-description"> {{ $flha->supervisor_name }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                                <div class="section-header-label"> JOB DESCRIPTION </div>
                                    <div class="content-description"> {{ $flha->job_description }} </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                                <div class="section-header-label"> SAFETY QUESTIONS </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Do you need to remove your gloves ?</div>
                </td>
                <td>
                                    <div class="content-description"> 
                                        @if ($flha->gloves_removed)
                                            {{ $flha->gloves_removed_description }}
                                        @else
                                            NO
                                        @endif
                                    </div>
                </td>
            </tr>
            <tr>
                <td>
                                    <div class="content-label">Are you working alone?</div>
                </td>
                <td>
                                    <div class="content-description"> 
                                        @if ($flha->working_alone)
                                            {{ $flha->working_alone_description }}
                                        @else
                                            NO
                                        @endif
                                    </div>
                </td>
            </tr>
            <tr>
                <td>
                                    <div class="content-label">Warning ribbon needed?</div>
                </td>
                <td>
                                    <div class="content-description"> 
                                        @if ($flha->warning_ribbon)
                                            {{ $flha->warning_ribbon_description }}
                                        @else
                                            NO
                                        @endif
                                    </div>
                                </div>
                            </div>
                </td>
            </tr>
            @if ($flha->completion instanceOf JobCompletion)
                <tr>
                    <td colspan="2">
                        <div class="section-header-label"> JOB COMPLETION </div>
                    </td>
                </tr>
                <tr>
                    <td>
                                    <div class="content-label"> Permit closed? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->permit_closed?"YES":$flha->completion->permit_closed_description }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                                    <div class="content-label"> Hazard remaining? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->hazard_remaining?$flha->completion->hazard_remaining_description:"NO" }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                                    <div class="content-label"> Flagging removed? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->flagging_removed?"YES":$flha->completion->flagging_removed_description }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                                    <div class="content-label"> All incidents/injuries reported? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->incident_reported?"YES":$flha->completion->incident_reported_description }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                                    <div class="content-label"> Concerns addressed and documented? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->concerns?"YES":$flha->completion->concerns_description }} </div>
                    </td>
                </tr>
                <tr>
                    <td>                    
                                    <div class="content-label"> Tools/equipment removed? </div>
                    </td>
                    <td>
                                    <div class="content-description"> {{ $flha->completion->equipment_removed?"YES":$flha->completion->equipment_removed_description }} </div>
                    </td>
                </tr>
            @endif
        </table>

@if($flha->tasks()->count())
<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> TASK OBSERVED </span>
        </div>
    </div>
</div>
        <table>
            <?php 
                $risklevel = array('low-risk','medium-risk','high-risk');
            ?>
            @foreach ($flha->tasks as $t)
                <tr>
                    <td class="text-success" colspan="2"> {{ $t->title }} </td>
                </tr>
                <tr>
                    <td class="tbl-head"> Hazard Details </td>
                    <td class="tbl-head"> Eliminate/Control Hazard </td>
                </tr>
                @foreach ($t->hazards as $h)
                    <tr>
                        <td class="content-description {{ $risklevel[$h->risk_level] }}"> {{ $h->description }} </td>
                        <td class="content-description {{ $risklevel[$h->risk_assessment] }}"> {{ $h->eliminate_hazard }} </td>
                    </tr>
                @endforeach
            @endforeach 
        </table>
@endif

@if(count($flha->checklist))
<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> HAZARD CHECKLIST </span>
        </div>
    </div>
</div>
        <table>
            @foreach ($flha->checklist as $categoryId => $hazardList )
                <tr>
                    <td class="tbl-head" colspan='2'> {{ HazardChecklistCategory::find($categoryId)->category_name }} </td>
                </tr>
                    <?php $i=1; ?>
                    @foreach ( $hazardList as $h )
                        @if ($i%2 != 0)   
                            <tr>
                                <td>{{ $h->item_name }}</td>
                        @else
                                <td>{{ $h->item_name }}</td>
                            </tr>
                        @endif
                        <?php $i++; ?>
                    @endforeach
                    @if (count($hazardList) && $i%2 == 0)   
                        <td>&nbsp;</td></tr>
                    @endif
            @endforeach
        </table>
@endif


@if ($flha->signoffVisitors()->count())
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component"> VISITORS </span>
            </div>
        </div>
    </div>
    <table class="bordered">
        <thead>
            <tr>
                <th style="height:45px;"> Visitor name </th>
                <th style="height:45px;"> Signature </th>
            </tr>
        </thead>
        <tbody>
        @foreach ( $flha->signoffVisitors as $v )
                <tr>
                    <td class="center">
                        {{ $v->first_name.' '.$v->last_name }} 
                    </td>
                    <td class="center">
                        <?php $signature = $sm->getSignoffVisitorSignature($v); ?>
                        @if (is_file($signature))
                            <img src="{{ URL::to('image/flha/visitor/'.$v->signoff_visitor_id.'/signature') }} " class="signature-image">
                        @endif
                    </td>
                </tr>
        @endforeach
        </tbody>
    </table>
@else 
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component"> VISITORS (N/A)</span>
            </div>
        </div>
    </div>
@endif

@if ($flha->signoffWorkers()->count())
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component"> WORKERS</span>
            </div>
        </div>
    </div>
    <table class="bordered">
        <thead>
            <tr>
                <th style="height:45px;"> Worker Name </th>
                <th style="height:45px;"> Breaks </th>
                <th style="height:45px;"> Signature </th>
            </tr>
        </thead>
        @foreach ($flha->signoffWorkers as $w)
            <tr>
                    <td class="center">
                        {{ $w->first_name.' '.$w->last_name }} 
                    </td>
                    <td class="center">
                        @foreach ( $w->breaks as $b )
                            {{ $b->created_at.' ('.$b->getType().' break)' }} <br/>
                        @endforeach
                    </td>
                    <td class="center">
                        <?php $signature = $sm->getSignoffWorkerSignature($w); ?>
                        @if (is_file($signature))
                            <img src="{{ URL::to('image/flha/worker/'.$w->signoff_worker_id.'/signature') }} " class="signature-image">
                        @endif
                    </td>
            </tr>
        @endforeach
    </table>
@else
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component"> WORKERS (N/A)</span>
            </div>
        </div>
    </div>
@endif

@if($flha->spotchecks()->count())
<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> SPOTCHECKS </span>
        </div>
    </div>
</div>
        @foreach ($flha->spotchecks as $s)
            <table class="bordered">
                <tr>
                    <td class="tbl-head center"> {{ $s->first_name.' '.$s->last_name }} - {{ $s->position.' at '.$s->company }} ( Created on : {{ $s->created_at}} ) </td>
                    <td class="tbl-head center">   <?php $signature = $sm->getSpotcheckSignaturePhoto($s); ?>
                            @if (is_file($signature))
                                <img src="{{ URL::to('image/flha/spotcheck/'.$s->spotcheck_id.'/signature') }} " class="signature-image">
                            @endif
                    </td>
                </tr>
                <tr>
                    <td class="center" colspan='2'> Spotcheck details </td>
                </tr>
                
                <tr>
                    <td> Is the current FLHA valid for the tasks? </td>
                    <td> {{$s->flha_validity==1?"Yes":"No"}}</td>
                </tr>
                @if ($s->flha_validity ==0 )
                   <tr>
                       <td> FLHA validity description </td>
                       <td> {{$s->flha_validity_description}}</td>
                   </tr>   
                @endif
                
                <tr>
                    <td> Critical hazard identified? </td>
                    <td> {{$s->critical_hazard==1?"Yes":"No"}}</td>
                </tr>
                @if ($s->critical_hazard == 1 )
                   <tr>
                       <td> Critical hazard description </td>
                       <td> {{$s->critical_hazard_description}}</td>
                   </tr>   
                @endif
                
                
                <tr>
                    <td> Crew list complete? </td>
                    <td> {{$s->crew_list_complete==1?"Yes":"No"}}</td>
                </tr>
                @if ($s->crew_list_complete ==0 )
                   <tr>
                       <td> Crew list description </td>
                       <td> {{$s->crew_description}}</td>
                   </tr>   
                @endif
            </table><br/>
        @endforeach
@endif

@if($flha->photos()->count())
    <div id="pdf-image-container">
        @foreach ($flha->photos as $photo) 
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
                        <div class="content-description"> {{ $flha->addedBy->first_name.' '.$flha->addedBy->last_name }} </div>
                    </td>
                    <td>
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
                   @if ($flha->review)
                   <td> 
                        <div class="details-content">
                                <div class="content-label"> Reviewed by: 
                                    <span class="content-description"> {{ $flha->review->reviewer_name }} </span>
                                    <br/>
                                    On:
                                    <span class="content-description"> {{ WKSSDate::display($flha->review->ts,$flha->review->created_at) }} </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="details-content">
                                <div class="content-label"> Signature  </div>
                                @if ($flha->review->signature())
                                    <div class="content-description center"> 
                                        <img src="{{ URL::to('image/review/signature',$flha->review->form_review_id ) }}" class="admin-signature-image">
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
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
@if ($tailgate->addedBy->company->logo())
    <img src="{{Photo::generic($tailgate->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif

<div style="text-align:center">
   <h2> {{ $tailgate->addedBy->company->company_name }} </h2>
   <h4> Tailgate Card </h4>         
</div>

<div class="list-container">
    <div class="list-container-header">
      <div class="list-component-container">
          <span class="list-component">{{ $tailgate->title }}</span>
          <span class="right-header-text"> ({{ $tailgate->created_at }})  </span>
      </div>
    </div>
</div>
    <table>
            <tr>
                <td colspan="2">
                    <div class="section-header">
                        <div class="section-header-label"> JOB DESCRIPTION </div>
                        <div class="details-content">
                            <div class="content-description"> {{ $tailgate->job_description }} </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                        <div class="section-header-label"> TAILGATE DETAILS </div>
                <td>
            </tr>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Supervisors</div>
                </td>
                <td>
                    <div class="content-description"> {{ implode(", ",array_pluck($tailgate->supervisors()->get()->all(),'name')) }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Phone number</div>
                </td>
                <td>
                    <div class="content-description"> {{ $tailgate->phone_number }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Job number</div>
                </td>
                <td>
                    <div class="content-description"> {{ $tailgate->job_number }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Permit numbers</div>
                </td>
                <td>
                    <div class="content-description"> {{ implode(", ",array_pluck($tailgate->permits()->get()->all(),'permit_number')) }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">STARS Site</div>
                </td>
                <td>
                    <div class="content-description"> {{ $tailgate->stars_site }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Locations</div>
                </td>
                <td>
                    <div class="content-description"> {{ implode(", ",array_pluck($tailgate->locations()->get()->all(),'location')) }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">LSDs</div>
                </td>
                <td>
                    <div class="content-description"> {{ implode(", ",array_pluck($tailgate->lsds()->get()->all(),'lsd')) }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Additional comments</div>
                </td>
                <td>
                    <div class="content-description"> {{ $tailgate->comment }} </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="content-label">Type of assessment</div>
                </td>
                <td>
                    <div class="content-description"> {{ $tailgate->getTypeOfAssessment() }} </div>
                </td>
            </tr>
            @if ($tailgate->completion instanceOf JobCompletion)
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
                                <div class="content-description"> {{ $tailgate->completion->permit_closed?"YES":$tailgate->completion->permit_closed_description }} </div>
                <td>
            </tr>
            <tr>
                <td>
                                <div class="content-label"> Hazard remaining? </div>
                </td>
                <td>
                                <div class="content-description"> {{ $tailgate->completion->hazard_remaining?$tailgate->completion->hazard_remaining_description:"NO" }} </div>
                <td>
            </tr>
            <tr>
                <td>
                                <div class="content-label"> Flagging removed? </div>
                </td>
                <td>
                                <div class="content-description"> {{ $tailgate->completion->flagging_removed?"YES":$tailgate->completion->flagging_removed_description }} </div>
                <td>
            </tr>
            <tr>
                <td>
                                <div class="content-label"> All incidents/injuries reported? </div>
                </td>
                <td>
                                <div class="content-description"> {{ $tailgate->completion->incident_reported?"YES":$tailgate->completion->incident_reported_description }} </div>
                <td>
            </tr>
            <tr>
                <td>
                                <div class="content-label"> Concerns addressed and documented? </div>
                </td>
                <td>
                                <div class="content-description"> {{ $tailgate->completion->concerns?"YES":$tailgate->completion->concerns_description }} </div>
                <td>
            </tr>
            <tr>
                <td>
                                <div class="content-label"> Tools/equipment removed? </div>
                </td>
                <td>
                                <div class="content-description"> {{ $tailgate->completion->equipment_removed?"YES":$tailgate->completion->equipment_removed_description }} </div>
                <td>
            </tr>
            @endif
</table>

@if($tailgate->tasks()->count())
<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> TASK OBSERVED </span>
        </div>
    </div>
    <div class="list-container-body">
        <table>
            <?php 
                $risklevel = array('low-risk','medium-risk','high-risk');
            ?>
            @foreach ($tailgate->tasks as $t)
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
    </div>
</div>   
@endif

@if(count($tailgate->checklist))
<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> HAZARD CHECKLIST </span>
        </div>
    </div>
    <div class="list-container-body">
        <table>
            @foreach ($tailgate->checklist as $categoryId => $hazardList )
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
    </div>
</div>   
@endif


<div class="list-container">
    <div class="list-container-header">
        <div class="list-component-container">
            <span class="list-component"> ASSESSMENT CHECKLIST </span>
        </div>
    </div>
    <div class="list-container-body">
        <div class="content-label"> Is everyone fit for duty? </div>
        <div class="content-description">  {{ $tailgate->fit_for_duty?"YES":"NO <br/> ".$tailgate->fit_for_duty_description }} </div>
        <div class="content-label"> Is everyone properly trained/qualified to do their job? </div>
        <div class="content-description">  {{ $tailgate->proper_training?"YES":"NO <br/>  ".$tailgate->proper_training_description }} </div>
        <div class="content-label"> Has the job scope and procedure(s) have been discussed with everyone on location? </div>
        <div class="content-description">  {{ $tailgate->job_scope_and_procedures?"YES":"NO <br/>  ".$tailgate->job_scope_and_procedures_description }} </div>
        <div class="content-label"> Have the hazards specific to the job been identified? </div>
        <div class="content-description">  {{ $tailgate->hazards_identified?"YES":"NO <br/>  ".$tailgate->hazards_identified_description }} </div>
        <div class="content-label"> Have the required hazard controls been implemented and confirmed? </div>
        <div class="content-description">  {{ $tailgate->controls_implemented?"YES":"NO <br/>  ".$tailgate->controls_implemented_description }} </div>
    </div>
</div>   

@if ($tailgate->signoffVisitors()->count())
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
        @foreach ( $tailgate->signoffVisitors as $v )
            <tr>
                <td class="center">
                    {{ $v->first_name.' '.$v->last_name }} 
                </td>
                <td class="center">
                    <?php $signature = $sm->getTailgateSignoffVisitorSignature($v); ?>
                    @if (is_file($signature))
                        <img src="{{ URL::to('image/tailgates/visitor/'.$v->signoff_visitor_id.'/signature') }} " class="signature-image">
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

@if ($tailgate->signoffWorkers()->count())
    <div class="list-container">
        <div class="list-container-header">
            <div class="list-component-container">
                <span class="list-component"> WORKERS </span>
            </div>
        </div>
    </div>
    <table class="bordered">
        <thead>
            <tr>
                <th style="height:45px;"> Worker name </th>
                <th style="height:45px;"> Signature </th>
            </tr>
        </thead>
        <tbody>
        @foreach ($tailgate->signoffWorkers as $w)
            <tr>
                <td class="center">
                    {{ $w->first_name.' '.$w->last_name }} 
                </td>
                <td class="center">
                    <?php $signature = $sm->getTailgateSignoffWorkerSignature($w); ?>
                    @if (is_file($signature))
                        <img src="{{ URL::to('image/tailgates/worker/'.$w->signoff_worker_id.'/signature') }} " class="signature-image">
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
                <span class="list-component"> WORKERS (N/A) </span>
            </div>
        </div>
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
                <div class="content-description"> {{ $tailgate->addedBy->first_name.' '.$tailgate->addedBy->last_name }} </div>
            </td>
            <td>
                <div class="details-content">
                    <div class="content-label"> Signature  </div>
                        @if ($tailgate->addedBy->signature())
                            <div class="content-description center"> 
                                <img src="{{ URL::to('image/worker/signature',$tailgate->addedBy->auth_token) }} " class="signature-image">
                            </div>
                        @else
                            <div class="content-description"> 
                                No signature available
                            </div>
                        @endif
                    </div>
                </div> 
           </td>
           @if ($tailgate->review)
           <td> 
                <div class="details-content">
                        <div class="content-label"> Reviewed by: 
                            <span class="content-description"> {{ $tailgate->review->reviewer_name }} </span>
                            <br/>
                            On:
                            <span class="content-description"> {{ WKSSDate::display($tailgate->review->ts,$tailgate->review->created_at) }} </span>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="details-content">
                        <div class="content-label"> Signature  </div>
                        @if ($tailgate->review->signature())
                            <div class="content-description center"> 
                                <img src="{{ URL::to('image/review/signature',$tailgate->review->form_review_id ) }}" class="admin-signature-image">
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
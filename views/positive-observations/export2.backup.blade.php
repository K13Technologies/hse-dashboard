@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">
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
    </style>
@stop

@section('content')
@if ($po->addedBy->company->logo())
    <img src="{{Photo::generic($po->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $po->addedBy->company->company_name }} </h2>
   <h4> Field Observation Card </h4>
</div>     
<div class="list-container pb">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">{{ $po->title }}</span>
                  <span class="right-header-text"> ({{ $po->created_at }}) </span>
              </div>
            </div>
            <table  class="list-container-body">
                <tbody>
                <tr>
                    <td>
                         <div class="section-header">
                            <div class="section-header-label"> PERSON OBSERVED </div>
                            <div class="details-content">
                                <div class="content-label">Name</div>
                                <div class="content-description"> 
                                    {{ implode(', ',array_pluck($po->personsObserved->toArray(),'name')) }}
                                </div>
                                <div class="content-label">Company</div>
                                 <div class="content-description">
                                     @if (count($po->personsObserved))
                                             {{ $po->personsObserved->first()->company_name }}
                                     @endif
                                     </div>
                            </div>
                        </div> 
                        <div class="section-header">
                             <div class="section-header-label"> LOCATION </div>
                             <div class="details-content">
                                 <div class="content-label">Site</div>
                                 <div class="content-description"> {{ $po->site }} </div>
                                 <div class="content-label">Specific location</div>
                                 <div class="content-description">{{ $po->specific_location }}</div>
                                 <div class="content-label">LSD</div>
                                 <div class="content-description">{{ $po->lsd }}</div>
                                 <div class="content-label">Wellpad</div>
                                 <div class="content-description">{{ $po->wellpad }}</div>
                                 <div class="content-label">Road or intersection </div>
                                 <div class="content-description">{{ $po->road }}</div>
                             </div>
                         </div> 
                        <div class="section-header">
                                <div class="section-header-label"> ACTIVITY </div>
                                <div class="details-content">
                                    <div class="content-description"> 
                                        @if ($po->activity)
                                            {{ $po->activity->activity_name }} 
                                        @endif
                                    </div>
                                </div>
                            </div>  
                           
                    </td>
                    
                    <td>
                        <div class="section-header">
                                <div class="section-header-label"> DESCRIPTiON </div>
                                <div class="details-content">
                                    <div class="content-description"> {{ $po->description }} </div>
                                </div>
                        </div>  
                        <div class="section-header">
                            <div class="section-header-label"> OBSERVATION </div>
                            <div class="details-content">
                                <div class="content-label"> Is this a positive observation?</div>
                                <div class="content-description">{{ $po->is_positive_observation?"YES":"NO" }}  </div>
                            @if ($po->is_positive_observation)
                                       <div class="content-label">Describe observation</div>
                                       <div class="content-description"> {{ $po->is_po_details }} </div>
                            @else
                                       <div class="content-label">Corrective action for at risk behaviour</div>
                                       <div class="content-description"> {{ $po->is_po_details }} </div>
                                       <div class="content-label">Correct at risk behaviour on site </div>
                                       <div class="content-description"> {{ $po->correct_on_site?"YES":"NO" }} </div>
                                       @if (!$po->correct_on_site)
                                                    <div class="content-label"> Required action set by admin </div>
                                                    <div class="content-description"> {{ $po->action?$po->action:"N/A" }} </div>
                                                    <div class="content-label"> Status </div>
                                                    <div class="content-description"> 
                                                        @if ($po->completed_on != NULL)
                                                                Completed on <b> {{ $po->completed_on }}</b>
                                                        @else
                                                                <b> Outstanding </b> 
                                                        @endif
                                                    </div>
                                       @endif
                            @endif
                            </div>
                        </div> 
                    </td>
                    
                    <td>
                                <div class="section-header">
                                    <div class="section-header-label"> COMMENTS </div>
                                    <div class="details-content">
                                        <div class="content-description"> {{ $po->comment }} </div>
                                    </div>
                                </div> 
                        
                        
            
                        
                        <div class="section-header">
                          <div class="section-header-label"> PERFORMED BY</div>
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
                        
                        @if ($po->review)
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
                    </td>
                </tr>
                </tbody>
            </table>
       
</div>        

@if ($po->task_1_title || $po->task_1_description)
<div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">TASKS OBSERVED  </span>
              </div>
            </div>
                <div class="list-container-body">
                    <div id="inspection-details-container">
                        <table>
                        <tbody>
                            <tr>
                                <td class="inspection-header-label tbl-head"> Title </td>
                                <td class="inspection-header-label tbl-head"> Description </td>
                            </tr>
                            @if ($po->task_1_title || $po->task_1_description)
                                <tr>
                                    <td> <div class="content-description"> {{ $po->task_1_title }} </div> </td>
                                    <td> <div class="content-description"> {{ $po->task_1_description }} </div> </td>
                                 </tr>
                            @endif
                            @if ($po->task_2_title || $po->task_2_description)
                                <tr>
                                    <td> <div class="content-description"> {{ $po->task_2_title }} </div> </td>
                                    <td> <div class="content-description"> {{ $po->task_2_description }} </div> </td>
                                </tr>
                            @endif
                            @if ($po->task_3_title || $po->task_3_description)
                                <tr>
                                    <td> <div class="content-description"> {{ $po->task_3_title }} </div> </td>
                                    <td> <div class="content-description"> {{ $po->task_3_description }} </div> </td>
                                </tr>
                            @endif
                        </tbody>
                    </table> 
                    </div>
                </div>
        </div>
@endif

@if (count($po->positiveObservationCategories))
<div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component"> HAZARD CATEGORIES </span>
              </div>
            </div>
                <div class="list-container-body">
                    <div id="inspection-details-container">
                        <table>
                        <tbody>
                           <?php $i=1; ?>
                            @foreach ( $po->positiveObservationCategories as $poc )
                                @if ($i%2 != 0)   
                                    <tr>
                                        <td>{{ $poc->category_name }}</td>
                                @else
                                        <td>{{ $poc->category_name }}</td>
                                    </tr>
                                @endif
                                <?php $i++; ?>
                            @endforeach
                            @if (count($po->positiveObservationCategories) && $i%2 == 0)   
                                <td>&nbsp;</td></tr>
                            @endif
                        </tbody>
                    </table> 
                    </div>
                </div>
        </div>
@endif

@if($po->photos()->count())
    <div id="pdf-image-container">
       @foreach ($po->photos as $photo) 
            <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
       @endforeach 
    </div>
@endif

@stop
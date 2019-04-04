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
        .content-label{
            padding:0;
        }
        table>tbody>tr>td{
            padding:10px;
            vertical-align: top;
            /*min-width: 200px;*/
        }
        .list-container{
            page-break-inside:avoid;
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
<div class="list-container">
    <div class="list-container-header">
      <div class="list-component-container">
          <span class="list-component">{{ $po->title }}</span>
          <span class="right-header-text"> ({{ $po->created_at }})  </span>
      </div>
    </div>
</div>
            <table>
                <tbody>
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Location and Description</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Site</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->site }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Specific location</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $po->specific_location }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">LSD</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $po->lsd }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Wellpad</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $po->wellpad }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Road or intersection </div>
                    </td>
                    <td>
                        <div class="content-description">{{ $po->road }}</div>
                    </td>
                </tr>
                <tr>        
                    <td>
                        <div class="content-label"> Description </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->description }} </div>
                    </td>
                </tr>

                
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Persons Observed and Activity</div>
                    </td>
                </tr>

                <tr>        
                    <td>
                        <div class="content-label"> Activity </div>
                        <div class="content-description"> {{ $po->activity->activity_name }} </div>
                    </td>
                    <td>
                        <div class="content-label"> Persons Observed </div>
                        @foreach ($po->personsObserved as $p)
                            <div class="content-description">
                               {{ $p->name }} ({{ $p->company }})
                            </div>
                        @endforeach
                    </td>
                </tr>
                
                
                <tr>
                    <td colspan="2">
                        <div class="section-header-label"> Observation & Corrective Action</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Is this a positive observation?</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->is_positive_observation?"YES":"NO" }} </div>
                    </td>
                </tr>
                @if ($po->is_positive_observation)
                <tr>
                    <td>
                        <div class="content-label"> Describe observation</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->is_po_details }} </div>
                    </td>
                </tr>
                @else
                <tr>
                    <td>
                        <div class="content-label"> Corrective action for at risk behaviour </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->is_po_details }}  </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Correct at risk behaviour on site </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $po->correct_on_site?"YES":"NO" }} </div>
                    </td>
                </tr>
                    @if (!$po->correct_on_site)
                    <tr>
                        <td>
                            <div class="content-label"> Required action set by admin </div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $po->action?$po->action:"N/A" }} </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="content-label"> Status </div>
                        </td>
                        <td>
                            <div class="content-description"> 
                                @if ($po->completed_on != NULL)
                                        Completed on <b> {{ $po->completed_on }}</b>
                                @else
                                        <b> Outstanding </b> 
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                @endif
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Comments</div>
                            <div class="content-description"> {{ $po->comment }} </div>
                    </td>
                </tr>
                

@if (count($po->positiveObservationCategories))
    <tr>
        <td colspan="2">
            <div class="section-header-label"> Hazard Categories</div>
        </td>
    </tr>
                   <?php $i=1; ?>
                    @foreach ( $po->positiveObservationCategories as $hc )
                        @if ($i%2 != 0)   
                            <tr>
                                <td>{{ $hc->category_name }}</td>
                        @else
                                <td>{{ $hc->category_name }}</td>
                            </tr>
                        @endif
                        <?php $i++; ?>
                    @endforeach
                    @if (count($po->positiveObservationCategories) && $i%2 == 0)   
                        <td>&nbsp;</td></tr>
                    @endif
            </div>
        </div>
@endif
    </tbody>
</table>

@if ($po->task_1_title || $po->task_1_description)
<div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">TASKS OBSERVED  </span>
              </div>
            </div>
</div>
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
@endif



@if($po->photos()->count())
    <div id="pdf-image-container">
       @foreach ($po->photos as $photo) 
            <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
       @endforeach 
    </div>
    <br/>
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
                        <div class="content-description"> {{ $po->addedBy->first_name.' '.$po->addedBy->last_name }} </div>
                    </td>
                    <td>
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
                   </td>
                   @if ($po->review)
                   <td> 
                        <div class="content-label"> Reviewed by: 
                            <span class="content-description"> {{ $po->review->reviewer_name }} </span>
                            <br/>
                            On:
                            <span class="content-description"> {{ WKSSDate::display($po->review->ts,$po->review->created_at) }} </span>
                        </div>
                    </td>
                    <td>
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
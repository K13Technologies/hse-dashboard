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
        .list-container{
            page-break-inside:auto;
        }
        table{ 
            page-break-inside:auto 
        }
    </style>
@stop

@section('content')
@if ($nearMiss->addedBy->company->logo())
    <img src="{{Photo::generic($nearMiss->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $nearMiss->addedBy->company->company_name }} </h2>
   <h4> Near Miss Card </h4>
</div>                        
   <div class="list-container pb">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">{{ $nearMiss->title }}</span>
                  <span class="right-header-text"> ({{ $nearMiss->created_at }})  </span>
              </div>
            </div>
             <table  class="list-container-body">
                <tbody>
                <tr>
                    <td>
                        <div class="section-header">
                            <div class="section-header-label"> LOCATION </div>
                            <div class="details-content">
                                <div class="content-label">Site</div>
                                <div class="content-description"> {{ $nearMiss->site }} </div>
                                <div class="content-label">Specific location</div>
                                <div class="content-description">{{ $nearMiss->specific_location }}</div>
                                <div class="content-label">LSD</div>
                                <div class="content-description">{{ $nearMiss->lsd }}</div>
                                <div class="content-label">Wellpad</div>
                                <div class="content-description">{{ $nearMiss->wellpad }}</div>
                                <div class="content-label">Road or intersection </div>
                                <div class="content-description">{{ $nearMiss->road }}</div>
                            </div>
                        </div>  
                    </td>
                    <td>
                            <div class="section-header">
                                 <div class="section-header-label"> Activity </div>
                                 <div class="details-content">
                                     <div class="content-description"> {{ $nearMiss->hazardActivity->activity_name }} </div>
                                 </div>
                             </div>  
                            <div class="section-header">
                                 <div class="section-header-label"> Description </div>
                                 <div class="details-content">
                                     <div class="content-description"> {{ $nearMiss->description }} </div>
                                 </div>
                             </div>  
                    
                            <div class="section-header">
                                <div class="section-header-label"> Corrective Action</div>
                                <div class="details-content">
                                    <div class="content-description"> {{ $nearMiss->corrective_action }} </div>
                                </div>
                                @if ($nearMiss->corrective_action_applied)
                                    <div class="section-header">
                                        <div class="section-header-label"> Corrective Action</div>
                                        <div class="details-content">
                                            <div class="content-description"> {{ $nearMiss->corrective_action_implementation }} </div>
                                        </div>
                                    </div>  
                                @else
                                    <div class="content-label">Corrective action not applied </div>
                                    <div class="content-description"> {{ $nearMiss->corrective_action_implementation }} </div>
                                    <div class="content-label"> Required action set by admin </div>
                                    <div class="content-description"> {{ $nearMiss->action?$nearMiss->action:"N/A" }} </div>
                                    <div class="content-label"> Status </div>
                                    <div class="content-description"> 
                                        @if ($nearMiss->completed_on != NULL)
                                                Completed on <b> {{ $nearMiss->completed_on }}</b>
                                        @else
                                                <b> Outstanding </b> 
                                        @endif
                                    </div>
                                @endif
                            </div>
                    </td>
                    <td>
                            <div class="section-header">
                                <div class="section-header-label"> Comments</div>
                                <div class="details-content">
                                    <div class="content-description"> {{ $nearMiss->comment }} </div>
                                </div>
                            </div> 
                        <div class="section-header">
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
                        </div> 
                                    <div class="section-header">
                        <div class="section-header-label"> Review by management</div>
                        
                        @if ($nearMiss->review)
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
                    </td>
                </tr>
                </tbody>
            </table>
    </div>   

@if (count($nearMiss->hazardCategories))
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
                            @foreach ( $nearMiss->hazardCategories as $hc )
                                @if ($i%2 != 0)   
                                    <tr>
                                        <td>{{ $hc->category_name }}</td>
                                @else
                                        <td>{{ $hc->category_name }}</td>
                                    </tr>
                                @endif
                                <?php $i++; ?>
                            @endforeach
                            @if (count($nearMiss->hazardCategories) && $i%2 == 0)   
                                <td>&nbsp;</td></tr>
                            @endif
                        </tbody>
                    </table> 
                    </div>
                </div>
        </div>
@endif


@if($nearMiss->photos()->count())
    <div id="pdf-image-container">
       @foreach ($nearMiss->photos as $photo) 
            <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
       @endforeach 
    </div>
@endif    

@stop
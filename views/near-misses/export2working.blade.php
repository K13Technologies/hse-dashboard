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
@if ($nearMiss->addedBy->company->logo())
    <img src="{{Photo::generic($nearMiss->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $nearMiss->addedBy->company->company_name }} </h2>
   <h4> Near Miss Card </h4>
</div>                        
<div class="list-container">
    <div class="list-container-header">
      <div class="list-component-container">
          <span class="list-component">{{ $nearMiss->title }}</span>
          <span class="right-header-text"> ({{ $nearMiss->created_at }})  </span>
      </div>
    </div>
</div>
            <table>
                <tbody>
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Location, Activity and Description</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Site</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->site }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Specific location</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $nearMiss->specific_location }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">LSD</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $nearMiss->lsd }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Wellpad</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $nearMiss->wellpad }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Road or intersection </div>
                    </td>
                    <td>
                        <div class="content-description">{{ $nearMiss->road }}</div>
                    </td>
                </tr>
                <tr>        
                    <td>
                        <div class="content-label"> Activity </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->hazardActivity->activity_name }} </div>
                    </td>
                </tr>
                <tr>        
                    <td>
                        <div class="content-label"> Description </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->description }} </div>
                    </td>
                </tr>
                <tr class="pb">
                    <td colspan="2">
                        <div class="section-header-label"> Corrective Action</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Required corrective action</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->corrective_action }} </div>
                    </td>
                </tr>
                @if ($nearMiss->corrective_action_applied)
                <tr>
                    <td>
                        <div class="content-label"> Applied corrective action</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->corrective_action_implementation }} </div>
                    </td>
                </tr>
                @else
                <tr>
                    <td>
                        <div class="content-label"> Corrective action not applied</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->corrective_action_implementation }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Required action set by admin </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $nearMiss->action?$nearMiss->action:"N/A" }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Status </div>
                    </td>
                    <td>
                        <div class="content-description"> 
                            @if ($nearMiss->completed_on != NULL)
                                    Completed on <b> {{ $nearMiss->completed_on }}</b>
                            @else
                                    <b> Outstanding </b> 
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Comments</div>
                            <div class="content-description"> {{ $nearMiss->comment }} </div>
                    </td>
                </tr>
                </tbody>

@if (count($nearMiss->hazardCategories))
    <tr>
        <td colspan="2">
            <div class="section-header-label"> Hazard Categories</div>
        </td>
    </tr>
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
            </div>
        </div>
    </table>
@endif


@if($nearMiss->photos()->count())
    <div id="pdf-image-container">
       @foreach ($nearMiss->photos as $photo) 
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
                        <div class="content-description"> {{ $nearMiss->addedBy->first_name.' '.$nearMiss->addedBy->last_name }} </div>
                    </td>
                    <td>
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
                   </td>
                   @if ($nearMiss->review)
                   <td> 
                        <div class="details-content">
                                <div class="content-label"> Reviewed by: 
                                    <span class="content-description"> {{ $nearMiss->review->reviewer_name }} </span>
                                    <br/>
                                    On:
                                    <span class="content-description"> {{ WKSSDate::display($nearMiss->review->ts,$nearMiss->review->created_at) }} </span>
                                </div>
                            </div>
                        <td/>
                        <td>
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
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
@if ($hazard->addedBy->company->logo())
    <img src="{{Photo::generic($hazard->addedBy->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $hazard->addedBy->company->company_name }} </h2>
   <h4> Hazard Card </h4>
</div>                        
<div class="list-container">
    <div class="list-container-header">
      <div class="list-component-container">
          <span class="list-component">{{ $hazard->title }}</span>
          <span class="right-header-text"> ({{ $hazard->created_at }})  </span>
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
                        <div class="content-description"> {{ $hazard->site }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Specific location</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $hazard->specific_location }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">LSD</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $hazard->lsd }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Wellpad</div>
                    </td>
                    <td>
                        <div class="content-description">{{ $hazard->wellpad }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label">Road or intersection </div>
                    </td>
                    <td>
                        <div class="content-description">{{ $hazard->road }}</div>
                    </td>
                </tr>
                <tr>        
                    <td>
                        <div class="content-label"> Activity </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $hazard->hazardActivity->activity_name }} </div>
                    </td>
                </tr>
                <tr>        
                    <td>
                        <div class="content-label"> Description </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $hazard->description }} </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="section-header-label"> Corrective Action</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Required corrective action</div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $hazard->corrective_action }} </div>
                    </td>
                </tr>
                @if ($hazard->corrective_action_applied)
                    <tr>
                        <td>
                            <div class="content-label"> Applied corrective action</div>
                        </td>
                        <td>
                            <div class="content-description"> Yes {{-- {{ $hazard->corrective_action_implementation }} --}} </div>
                        </td>
                    </tr>
                @else
                    <tr>
                        <td>
                            <div class="content-label"> Corrective action not applied reason</div>
                        </td>
                        <td>
                            <div class="content-description"> {{ $hazard->corrective_action_implementation }} </div>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td>
                        <div class="content-label"> Required action set by admin </div>
                    </td>
                    <td>
                        <div class="content-description"> {{ $hazard->action?$hazard->action:"N/A" }} </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="content-label"> Status </div>
                    </td>
                    <td>
                        <div class="content-description"> 
                            @if ($hazard->completed_on != NULL)
                                    Completed on <b> {{ $hazard->completed_on }}</b>
                            @else
                                    <b> Outstanding </b> 
                            @endif
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2">
                            <div class="section-header-label"> Comments</div>
                            <div class="content-description"> {{ $hazard->comment }} </div>
                    </td>
                </tr>
              

@if (count($hazard->hazardCategories))
    <tr>
        <td colspan="2">
            <div class="section-header-label"> Hazard Categories</div>
        </td>
    </tr>
                   <?php $i=1; ?>
                    @foreach ( $hazard->hazardCategories as $hc )
                        @if ($i%2 != 0)   
                            <tr>
                                <td>{{ $hc->category_name }}</td>
                        @else
                                <td>{{ $hc->category_name }}</td>
                            </tr>
                        @endif
                        <?php $i++; ?>
                    @endforeach
                    @if (count($hazard->hazardCategories) && $i%2 == 0)   
                        <td>&nbsp;</td></tr>
                    @endif
            </div>
        </div>
   
@endif
  </tbody>
</table>


@if($hazard->photos()->count())
    <div id="pdf-image-container">
       @foreach ($hazard->photos as $photo) 
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
                        <div class="content-description"> {{ $hazard->addedBy->first_name.' '.$hazard->addedBy->last_name }} </div>
                    </td>
                    <td>
                        <div class="details-content">
                            <div class="content-label"> Signature  </div>
                                @if ($hazard->addedBy->signature())
                                    <div class="content-description center"> 
                                        <img src="{{ URL::to('image/worker/signature',$hazard->addedBy->auth_token) }} " class="signature-image">
                                    </div>
                                @else
                                    <div class="content-description"> 
                                        No signature available
                                    </div>
                                @endif
                            </div>
                        </div> 
                   </td>
                   @if ($hazard->review)
                   <td> 
                        <div class="details-content">
                                <div class="content-label"> Reviewed by: 
                                    <span class="content-description"> {{ $hazard->review->reviewer_name }} </span>
                                    <br/>
                                    On:
                                    <span class="content-description"> {{ WKSSDate::display($hazard->review->ts,$hazard->review->created_at) }} </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="details-content">
                                <div class="content-label"> Signature  </div>
                                @if ($hazard->review->signature())
                                    <div class="content-description center"> 
                                        <img src="{{ URL::to('image/review/signature',$hazard->review->form_review_id ) }}" class="admin-signature-image">
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
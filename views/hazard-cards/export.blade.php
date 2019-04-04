@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/nivo-slider.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/wkss/slider.css') }}}" rel="stylesheet">
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
                <span class="list-component">{{ $hazard->title }} </span>
                <span class="right-header-text"> ({{ $hazard->created_at }})  </span>
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
                                <div class="content-description"> {{ $hazard->site }} </div>
                                <div class="content-label">Specific location</div>
                                <div class="content-description">{{ $hazard->specific_location }}</div>
                                <div class="content-label">LSD</div>
                                <div class="content-description">{{ $hazard->lsd }}</div>
                                <div class="content-label">Wellpad</div>
                                <div class="content-description">{{ $hazard->wellpad }}</div>
                                <div class="content-label">Road or intersection </div>
                                <div class="content-description">{{ $hazard->road }}</div>
                            </div>
                        </div>  
                    </td>
                    <td>
                        <div class="section-header">
                            <div class="section-header-label"> Activity </div>
                                <div class="details-content">
                                    <div class="content-description"> 
                                        @if ($hazard->hazard_activity)
                                            {{ $hazard->hazard_activity->activity_name }} 
                                        @endif
                                    </div>
                                </div>
                            </div>  
                           <div class="section-header">
                                <div class="section-header-label"> Description </div>
                                <div class="details-content">
                                    <div class="content-description"> {{ $hazard->description }} </div>
                                </div>
                            </div>  
                           <div class="section-header">
                                <div class="section-header-label"> Hazard Categories </div>
                                <div class="details-content">
                                    @foreach ($hazard->hazardCategories as $hc)
                                        <div class="content-description"> {{ $hc->category_name }} </div>
                                    @endforeach
                                </div>
                            </div>  
                    </td>
                    <td>
                         <div class="section-header">
                            <div class="section-header-label"> Corrective Action</div>
                            <div class="details-content">
                                <div class="content-description"> {{ $hazard->corrective_action }} </div>
                            </div>
                        </div> 
                        @if ($hazard->corrective_action_applied)
                            <div class="section-header">
                                <div class="section-header-label"> Corrective Action</div>
                                <div class="details-content">
                                    <div class="content-description"> {{ $hazard->corrective_action_implementation }} </div>
                                </div>
                            </div>  
                        @endif
                        <div class="section-header">
                            <div class="section-header-label"> Comments</div>
                            <div class="details-content">
                                <div class="content-description"> {{ $hazard->comment }} </div>
                            </div>
                        </div> 
                        <div class="section-header">
                          <div class="section-header-label"> Performed by </div>
                            <div class="details-content">
                                <div class="content-label"> Worker name  </div>
                                <div class="content-description"> {{ $hazard->addedBy->first_name.' '.$hazard->addedBy->last_name }} </div>
                            </div>
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
                </tr>
                </tbody>
            </table>
        </div>   
@if($hazard->photos()->count())
    <div id="pdf-image-container">
       @foreach ($hazard->photos as $photo) 
            <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
       @endforeach 
    </div>
@endif

@stop
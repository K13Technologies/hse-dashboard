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
                    </td>
                    
                    <td>
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
                           <div class="section-header">
                                <div class="section-header-label"> TASK OBSERVED</div>
                                <div class="details-content">
                                            @if ($po->task_1_title || $po->task_1_description)
                                                    <div class="content-label">Title</div>
                                                    <div class="content-description"> {{ $po->task_1_title }} </div>
                                                    <div class="content-label">Description</div>
                                                    <div class="content-description"> {{ $po->task_1_description }} </div>
                                            @endif
                                             @if ($po->task_2_title || $po->task_2_description)
                                                    <div class="content-label">Title</div>
                                                    <div class="content-description"> {{ $po->task_2_title }} </div>
                                                    <div class="content-label">Description</div>
                                                    <div class="content-description"> {{ $po->task_2_description }} </div>
                                            @endif
                                             @if ($po->task_3_title || $po->task_3_description)
                                                    <div class="content-label">Title</div>
                                                    <div class="content-description"> {{ $po->task_3_title }} </div>
                                                    <div class="content-label">Description</div>
                                                    <div class="content-description"> {{ $po->task_3_description }} </div>
                                                 </td>
                                            @endif
                                </div>
                            </div> 
                    </td>
                    
                    <td>
                                <div class="section-header">
                                    <div class="section-header-label"> Description </div>
                                    <div class="details-content">
                                        <div class="content-description"> {{ $po->description }} </div>
                                    </div>
                                </div>  
                               <div class="section-header">
                                    <div class="section-header-label"> Hazard Categories </div>
                                    <div class="details-content">
                                        @foreach ($po->positiveObservationCategories as $pc)
                                            <div class="content-description"> {{ $pc->category_name }} </div>
                                        @endforeach
                                    </div>
                                </div>  
                                <div class="section-header">
                                    <div class="section-header-label"> Comments</div>
                                    <div class="details-content">
                                        <div class="content-description"> {{ $po->comment }} </div>
                                    </div>
                                </div> 
                        <div class="section-header">
                          <div class="section-header-label"> Performed by </div>
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
                    </td>
                </tr>
                </tbody>
            </table>
       
</div>        
@if($po->photos()->count())
    <div id="pdf-image-container">
       @foreach ($po->photos as $photo) 
            <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
       @endforeach 
    </div>
@endif

@stop
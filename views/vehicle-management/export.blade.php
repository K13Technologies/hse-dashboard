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
    </style>
@stop

@section('content')
@if ($vehicle->company->logo())
    <img src="{{Photo::generic($vehicle->company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $vehicle->company->company_name }} </h2>
   <h4> Vehicle Inspection Card </h4>         
</div>
<br/>
        <div class="list-container">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">Vehicle Info</span>
              </div>
              <span class="pull-right">
              </span>
            </div>
            <div class="list-container-body">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="content-label">License plate</div>
                                    <div class="content-description"> {{ $vehicle->license_plate }} </div>
                                    <div class="content-label">Vehicle number</div>
                                    <div class="content-description"> {{ $vehicle->vehicle_number }} </div>
                                </td>
                                <td>
                                    <div class="content-label">Vehicle color</div>
                                    <div class="content-description"> {{ $vehicle->color }} </div>
                                    <div class="content-label">Mileage </div>
                                    <div class="content-description"> {{ $vehicle->mileage }} </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>    
            </div>
        </div>
    @if ($inspection)
       <div class="list-container" style="page-break-inside: avoid;page-break-before: auto">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">Inspection Info </span>
                  <span id="inspection-date-container" class="right-header-text"> ({{ $inspection->created_at }})  </span>
              </div>
            </div>
        
                <div class="list-container-body">
                    <div id="inspection-details-container">
                        <table>
                        <tbody>
                            <tr>
                                <td>
                                        <div class="inspection-section-header">
                                            <div class="inspection-header-label"> COMMENT </div>
                                            <div id="inspection-comment"> {{ $inspection->comment }}</div>
                                        </div>  
                                        <div class="inspection-section-header">
                                            <div class="inspection-header-label"> LOCATION </div>
                                            <div class="inspection-details-content">
                                                <div class="inspection-label">Location</div>
                                                <div id="inspection-location" class="content-description"> {{ $inspection->location }} </div>
                                                <div class="inspection-label">Mileage</div>
                                                <div id="inspection-mileage" class="content-description"> {{ $inspection->mileage }} </div>
                                            </div>
                                        </div>  
                                </td>
                                <td>
                                     <div class="inspection-header-label"> ENGINE </div>
                                        <div class="inspection-details-content" id="engine-content">
                                            @foreach($inspection->getProperties() as $p)
                                                  @if(preg_match("/^engine/",$p) && $inspection->$p!='')
                                                        <div class="inspection-label"> {{ ucwords(str_replace(array('engine_','_'),array('',' '),$p)) }}</div>
                                                        <div class="content-description"> {{ $inspection->$p }}</div>
                                                  @endif
                                            @endforeach

                                        </div>
                                        <div class="inspection-header-label"> INTERIOR </div>
                                        <div class="inspection-details-content" id="interior-content">
                                            @foreach($inspection->getProperties() as $p)
                                                  @if(preg_match("/^interior/",$p) && $inspection->$p!='')
                                                        <div class="inspection-label"> {{ ucwords(str_replace(array('interior','_'),array('',' '),$p)) }}</div>
                                                        <div class="content-description"> {{ $inspection->$p }}</div>
                                                  @endif
                                            @endforeach
                                        </div>
                                        <div class="inspection-header-label"> EXTERIOR </div>
                                        <div class="inspection-details-content" id="exterior-content">
                                            @foreach($inspection->getProperties() as $p)
                                                  @if(preg_match("/^visual/",$p) && $inspection->$p!='')
                                                        <div class="inspection-label"> {{ ucwords(str_replace(array('visual','_'),array('',' '),$p)) }}</div>
                                                        <div class="content-description"> {{ $inspection->$p }}</div>
                                                  @endif
                                            @endforeach
                                        </div>
                                  <div class="inspection-header-label"> Performed by </div>
                                    <div class="inspection-content">
                                        <div class="inspection-label"> Worker name  </div>
                                        <div class="content-description"> {{ $inspection->addedBy->first_name.' '.$inspection->addedBy->last_name }} </div>
                                    </div>
                                   <div class="inspection-content">
                                        <div class="inspection-label"> Signature  </div>
                                        @if ($inspection->addedBy->signature())
                                            <div class="content-description center"> 
                                                <img src="{{ URL::to('image/worker/signature',$inspection->addedBy->auth_token) }} " class="signature-image">
                                            </div>
                                        @else
                                            <div class="content-description"> 
                                                No signature available
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table> 
                    </div>
                </div>
        </div>
        
    @endif
    @if($inspection->photos()->count())
        <div id="pdf-image-container">
           @foreach ($inspection->photos as $photo) 
                <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
           @endforeach 
        </div>
    @endif
@stop

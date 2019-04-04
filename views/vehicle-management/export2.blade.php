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
        .tbl-head{
            font-weight: normal;
            max-width:300px;
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
        <div class="list-container pb">
            <div class="list-container-header">
              <div class="list-component-container">
                  <span class="list-component">Vehicle Info</span>
              </div>
            </div>
            <div class="list-container-body">
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <table >
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
                                            <div class="inspection-header-label"> COMMENT </div>
                                            
                                            <div id="inspection-comment"> {{ $inspection->comment }}</div>
                                            
                                            <div class="inspection-header-label"> LOCATION </div>
                                            
                                            <div class="content-label">Vehicle Mileage</div>
                                            <div class="content-description"> {{ $vehicle->mileage }} </div>
                                            
                                            <div class="content-label">Location </div>
                                            <div class="content-description"> {{ $vehicle->location }} </div>
                                </td>
                                <td>
                                     <div class="inspection-header-label"> Performed by </div>
                                        <div class="content-label"> Worker name  </div>
                                        <div class="content-description"> {{ $inspection->addedBy->first_name.' '.$inspection->addedBy->last_name }} </div>
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
                    <div class="section-header">
                        <div class="section-header-label"> Review by management</div>
                        
                        @if ($inspection->review)
                            <div class="details-content">
                                <div class="content-label"> Reviewed by: 
                                    <span class="content-description"> {{ $inspection->review->reviewer_name }} </span>
                                    <br/>
                                    On:
                                    <span class="content-description"> {{ WKSSDate::display($inspection->review->ts,$inspection->review->created_at) }} </span>
                                </div>
                            </div>
                            <div class="details-content">
                                <div class="content-label"> Signature  </div>
                                @if ($inspection->review->signature())
                                    <div class="content-description center"> 
                                        <img src="{{ URL::to('image/review/signature',$inspection->review->form_review_id ) }}" class="admin-signature-image">
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
        </div>
    @if ($inspection)
       <div class="list-container">
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
                                <td class="inspection-header-label" colspan="3"> ENGINE </td>
                            </tr>
                            <?php $issues = 0; ?>
                            @foreach($inspection->getEngineProperties() as $p)
                                @if ( $inspection->$p!='' )
                                    <?php $issues++;
                                          $currentAction =  $inspection->actions[$p]; ?>
                                    <tr>
                                        <td class="tbl-head">
                                                <div class="content-label"> {{ Inspection::getPartName($p) }} </div>
                                                <div class="inspection-description"> {{ $inspection->$p }}</div>
                                        </td>
                                        <td class="tbl-head"> 
                                            <div class="content-label"> Action </div>
                                            <div class="inspection-description"> {{ trim($currentAction->action)!=""?$currentAction->action:"N/A" }} </div>
                                        </td>
                                        <td class="tbl-head">
                                            <div class="content-label"> Status </div>
                                            <div class="inspection-description"> 
                                                @if ($currentAction->completed_on != NULL)
                                                    Completed on <b> {{ $currentAction->completed_on }}</b>
                                                @else
                                                    <b> Outstanding </b> 
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="tbl-head" >
                                                <div class="content-label"> {{ Inspection::getPartName($p) }} </div>
                                                <div class="inspection-description"> Passed. </div>
                                        </td>
                                        <td class="tbl-head" colspan="2"> 
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr>
                                <td class="inspection-header-label" colspan="3"> INTERIOR </td>
                            </tr>
                            <?php $issues = 0; ?>
                            @foreach($inspection->getInteriorProperties() as $p)
                                @if ( $inspection->$p!='' )
                                    <?php   $issues++;
                                            $currentAction =  $inspection->actions[$p]; ?>
                                    <tr>
                                        <td class="tbl-head">
                                                <div class="content-label"> {{ Inspection::getPartName($p) }} </div>
                                                <div class="inspection-description"> {{ $inspection->$p }}</div>
                                        </td>
                                        <td class="tbl-head"> 
                                            <div class="content-label"> Action </div>
                                            <div class="inspection-description"> {{ trim($currentAction->action)!=""?$currentAction->action:"N/A" }} </div>
                                        </td>
                                        <td class="tbl-head">
                                            <div class="content-label"> Status </div>
                                            <div class="inspection-description"> 
                                                @if ($currentAction->completed_on != NULL)
                                                    Completed on <b> {{ $currentAction->completed_on }}</b>
                                                @else
                                                    <b> Outstanding </b> 
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="tbl-head" >
                                                <div class="content-label"> {{ Inspection::getPartName($p) }}</div>
                                                <div class="inspection-description"> Passed. </div>
                                        </td>
                                        <td class="tbl-head" colspan="2"> 
                                        </td>
                                    </tr>
                                @endif
                           @endforeach
                            <tr>
                                <td class="inspection-header-label" colspan="3"> EXTERIOR </td>
                            </tr>
                            <?php $issues = 0; ?>
                            @foreach($inspection->getVisualProperties() as $p)
                                @if ( $inspection->$p!='' )
                                    <?php   $issues++;
                                            $currentAction =  $inspection->actions[$p]; ?>
                                    <tr>
                                        <td class="tbl-head">
                                                <div class="content-label"> {{ Inspection::getPartName($p) }}</div>
                                                <div class="inspection-description"> {{ $inspection->$p }}</div>
                                        </td>
                                        <td class="tbl-head"> 
                                            <div class="content-label"> Action </div>
                                            <div class="inspection-description"> {{ trim($currentAction->action)!=""?$currentAction->action:"N/A" }} </div>
                                        </td>
                                        <td class="tbl-head">
                                            <div class="content-label"> Status </div>
                                            <div class="inspection-description"> 
                                                @if ($currentAction->completed_on != NULL)
                                                    Completed on <b> {{ $currentAction->completed_on }}</b>
                                                @else
                                                    <b> Outstanding </b> 
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="tbl-head" >
                                                <div class="content-label"> {{ Inspection::getPartName($p) }} </div>
                                                <div class="inspection-description"> Passed. </div>
                                        </td>
                                        <td class="tbl-head" colspan="2"> 
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table> 
                    </div>
                </div>
        </div>
        
    @endif
    @if($inspection->photos()->count())
        <div id="pdf-image-container">
            @foreach($inspection->getProperties() as $p)
                @if (count($inspection->componentPhotos[$p]))
                    <div class="list-container-header bordered">
                      <div class="list-component-container">
                          <span class="list-component"> Photos for {{ Inspection::getPartName($p) }}  </span>
                      </div>
                    </div>
                    @foreach ($inspection->componentPhotos[$p] as $photo) 
                          <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
                    @endforeach 
                @endif
            @endforeach
        </div>
    @endif
@stop

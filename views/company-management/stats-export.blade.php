@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
     <style>
         #tbl{
             width:100%;
         }
         #tbl .list-container-body,#tbl .list-container-header{
             padding:5px;
         }
         .list-container{
             margin-bottom:5px;
         }
         .major-number{
            font-size:24px;
            color:#165ca2;
        }
        .minor-number{
            font-size:20px;
            color:#14a3d4;
        }
        .of-which{
            font-size:18px;
            color: #14a3d4;
        }
     </style>
@stop

@section('content')
@if ($company->logo())
    <img src="{{Photo::generic($company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $company->company_name }} </h2>
   <h4> Statistics Report for Project {{ $group->group_name }} </h4>
</div>                        
    <div class="list-container">
           <div class="list-container-header">
                <div class="list-component-container">
                    <?php $tf = function($timeframe) {
                                    switch($timeframe){
                                        case "weekly":
                                            return "A week before";
                                        case "monthly":
                                            return "A month before";
                                        case "yearly":
                                            return "A year before";
                                        case "forever":
                                            return "The beginning of time";
                                    }
                                }
                    ;?>
                    <span class="list-component"> Timespan : {{ $tf($timeframe) }} until {{ $refDate }} </span>
                </div>
           </div>
           <div class="list-container-body">
               <table id="tbl">
                   <thead></thead>
                   <tbody></tbody>
                   <tr>
                       <td>
                            <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Near Misses</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['nearMiss']['total'] }} </div>
                                    With corrective actions not implemented on site <br/>
                                    <div class="minor-number" >{{ $stats['nearMiss']['areq'] }} </div>
                                    Of which, with corrective actions implemented by admins<br/>
                                    <div class="of-which" >{{ $stats['nearMiss']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Hazards</span>
                                  </div>
                                </div>
                                 <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['hazard']['total'] }} </div>
                                    With corrective actions not implemented on site <br/>
                                    <div class="minor-number" >{{ $stats['hazard']['areq'] }} </div>
                                    Of which, with corrective actions implemented by admins<br/>
                                    <div class="of-which" >{{ $stats['hazard']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                   </tr>
                   <tr>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Field Observations</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['po']['total'] }} </div>
                                    With corrective actions not implemented on site <br/>
                                    <div class="minor-number" >{{ $stats['po']['areq'] }} </div>
                                    Of which, with corrective actions implemented by admins<br/>
                                    <div class="of-which" >{{ $stats['po']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Incidents</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['incident']['total'] }} </div>
                                    With corrective actions not implemented on site <br/>
                                    <div class="minor-number" >{{ $stats['incident']['areq'] }} </div>
                                    Of which, with corrective actions implemented by admins<br/>
                                    <div class="of-which" >{{ $stats['incident']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                   </tr>
                   <tr>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Daily Sign Ins</span>
                                  </div>
                                </div>
                                 <div class="list-container-body center">
                                    Total<br/>
                                    <div class="major-number" >{{ $stats['signin']['total'] }} </div>
                                    Total Sign Ins  <br/>
                                    <div class="minor-number" >{{ $stats['signin']['in'] }} </div>
                                    Total Sign Outs<br/>
                                    <div class="minor-number" >{{ $stats['signin']['out'] }} </div>
                                </div>
                            </div>
                       </td>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">FLHAs</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['flha']['total'] }} </div>
                                    Without Job Completions <br/>
                                    <div class="minor-number" >{{ $stats['flha']['areq'] }} </div>
                                    With Job Competions<br/>
                                    <div class="minor-number" >{{ $stats['flha']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                   </tr>
                   <tr>
                        <td>
                            <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Tailgates</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['tailgate']['total'] }} </div>
                                    Without Job Completions <br/>
                                    <div class="minor-number" >{{ $stats['tailgate']['areq'] }} </div>
                                    With Job Competions<br/>
                                    <div class="minor-number" >{{ $stats['tailgate']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                       <td>
                           <div class="list-container">
                                <div class="list-container-header">
                                  <div class="list-component-container">
                                      <span class="list-component">Inspections</span>
                                  </div>
                                </div>
                                <div class="list-container-body center">
                                    Total <br/>
                                    <div class="major-number" >{{ $stats['inspection']['total'] }} </div>
                                    With action required<br/>
                                    <div class="minor-number" >{{ $stats['inspection']['areq'] }} </div>
                                    OK or all actions completed<br/>
                                    <div class="minor-number" >{{ $stats['inspection']['afix'] }} </div>
                                </div>
                            </div>
                       </td>
                   </tr>
                   
                       
                   
                   
                   
                   
               </table>
           </div>
       </div>     
@stop
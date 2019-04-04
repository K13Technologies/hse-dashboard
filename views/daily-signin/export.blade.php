@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
@stop

@section('content')
@if ($company->logo())
    <img src="{{Photo::generic($company->logo()->name)}}" id='companyLogo'/> 
@endif
<div style="text-align:center">
   <h2> {{ $company->company_name }} </h2>
   <h4> Daily Sign In Report Project {{ $group->group_name }} </h4>
</div>                        
    <div class="list-container">
           <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component"> Report for {{ $signDate }} </span>
                </div>
           </div>
           <div class="list-container-body">
                <table class="table-list">
                    <thead>
                      <tr>
                        <th> Name </th>
                        <th> Sign-in times </th>
                        <th> Sign-out times </th>
                        <th> Sign-in signature </th>
                        <th> Sign-out signature </th>
                      </tr>
                    </thead>
                    <tbody id="dailySigninsTable">
                         @foreach ($signins as $s)
                            <tr>
                                <td>
                                    {{ $s['name'] }}
                                </td>
                                <td>
                                    @if (!empty($s['signins']))
                                        @foreach ($s['signins'] as $in)
                                            {{ $in->created_at }} <br/>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                     @if (!empty($s['signouts']))
                                        @foreach ($s['signouts'] as $out)
                                            {{ $out->created_at }} <br/>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    @if (!empty($s['signins']))
                                        <?php $in = last($s['signins']); ?>
                                        <img src='{{ URL::to("image/daily-signin/{$in->daily_signin_id}/signature")}}' class="signature-image"/>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                      @if (!empty($s['signouts']))
                                        <?php $out = last($s['signouts']); ?>
                                        <img src='{{ URL::to("image/daily-signin/{$out->daily_signin_id}/signature")}}' class="signature-image"/>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @endforeach  
                    </tbody>
                </table>
           </div>
       </div>     
@stop
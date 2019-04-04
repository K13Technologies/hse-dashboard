@extends('webApp::layouts.pdfExport')
@section('styles')
     @parent
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
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
        .list-container-header.bordered{
            border-top: 1px solid #d7e3f0;
        }
        tbody:before, tbody:after { 
            /*BOOTSTRAP OVERRIDE TO ALLOW FOR PROPER TABLE FORMATTING*/
            display: none !important; 
        }
        .section { page-break-after: always; }
    </style>
@stop

@section('content')

<div>
    @if ($ticket->worker->company->logo())
        <img src="{{Photo::generic($ticket->worker->company->logo()->name)}}" id='companyLogo'/> 
    @endif

    <h1 style="text-align:center">{{ $ticket->worker->company->company_name }} - Ticket</h1>
    <h2 style="text-align:center">Generated On: {{ date('d-M-Y') }}</h2>
    
    <hr/>
    <br/>

    <div class=''>
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component">{{ $ticket->type_name }} </span>
                    <span class="right-header-text">{{ $ticket->issued_internally ? " - Internally Issued" : " - Issued by: " . $ticket->issuer_organization_name }} </span>
                </div>
            </div>
        </div>
        <br/>

        <div class="content-label">Employee Name: </div>
        <div class="content-description">{{ $ticket->worker->first_name}} {{ $ticket->worker->last_name }}</div>

        <div class="content-label">Ticket Name: </div>
        <div class="content-description">{{ $ticket->type_name }}</div>

        <div class="content-label">Ticket Creation Date: </div>
        <div class="content-description">{{ WKSSDate::display(strtotime($ticket->created_at), $ticket->created_at, WKSSDate::FORMAT_LIST) }}</div>

        <div class="content-label">Ticket Expiry Date: </div>
        <div class="content-description"> {{ WKSSDate::timestampToStringWithCustomFormat($ticket->expiry_date, 'M d, Y') }} </div>
        
        <div class="content-label">Ticket Description: </div>
        <div class="content-description">{{ $ticket->description ? $ticket->description : "Not Applicable" }}</div>

        <div class="content-label">Approved by: </div>
        <div class="content-description">{{ $ticket->review->reviewer_name }}</div>

        <div class="content-label">Phone Number: </div>
        <?php $reviewer = Admin::findOrFail($ticket->review->added_by); ?>
        <div class="content-description">{{ $reviewer->phone_number }}</div>

        <div class="content-label">Date: </div>
        <div class="content-description">{{ WKSSDate::display($ticket->review->ts,$ticket->review->created_at, WKSSDate::FORMAT_LIST) }}</div>

        <div class="details-content">
            <div class="content-label"> Signature  </div>
            @if ($ticket->review->signature())
                <img src="{{ URL::to('image/review/signature',$ticket->review->form_review_id ) }}" class="admin-signature-image">
            @else
                <div class="content-description"> 
                    No signature available
                </div>
            @endif
        </div>

        <br/>
        @if($ticket->photos()->count())
            <div class="list-container">
                <div class="list-container-header">
                    <div class="list-component-container">
                        <span class="list-component">Ticket Photos </span>
                        <span class="right-header-text"></span>
                    </div>
                </div>
            </div>  
            <br/>
            <div id="pdf-image-container">
                @foreach ($ticket->photos as $photo) 
                    <img src="{{ Photo::generic($photo->name) }}" class="pdf-image"/>
                @endforeach 
            </div>
        @endif
        <br/>
    </div>

</div>
@stop
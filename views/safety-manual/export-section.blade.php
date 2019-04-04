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
    @if ($manual->company->logo())
        <img src="{{Photo::generic($manual->company->logo()->name)}}" id='companyLogo'/> 
    @endif

    <h1 style="text-align:center">{{ $manual->company->company_name }} - Safety Manual</h1>
    <h2 style="text-align:center">Generated On: {{ date('d-M-Y') }}</h2>
    
    <hr/>
    <br/>

    <div class=''>
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component">{{ $section->section_title }} </span>
                    <span class="right-header-text"></span>
                </div>
            </div>
        </div>
        <br/>

        @foreach($section->subsections as $subsection)
            <div class="section-header-label"> {{ $subsection->subsection_title }}</div>
            <br/>
            {{ $subsection->subsection_content}}
            <br/>
        @endforeach

        <br/>
    </div>

</div>
@stop
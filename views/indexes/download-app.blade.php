@extends('webApp::layouts.noNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/auth/auth.css') }}}" rel="stylesheet">
     <style>
         .container{
             width:auto;
             min-width:600px;
             min-height: 900px;
         }
         #noNavLeftLine,#noNavRightLine{
             margin-bottom:29px
         }
     </style>
@stop


@section('page-title')
    White Knight 
    <br/> 
    <br/> 
    Safety Solutions
@stop


@section('content')
    <a href="itms-services://?action=download-manifest&url={{URL::to('WhiteKnight.plist')}}">
        <button class="btn-orange">Install application</button>
    </a>
@stop

@section('footer')
    @parent
@stop
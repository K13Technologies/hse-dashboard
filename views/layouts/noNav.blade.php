@extends('webApp::layouts.master')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/noNav.css') }}}" rel="stylesheet">
@stop

@section('body')
    <div id='container-fluid'>
        <div class='row' id="noNavTitleContainer">
            <div class='col-md-12'>
                <div class='row'>
                    <img src="{{ asset('assets/img/wkss/logo_login.png') }}"/>
                </div>
                <div class='row'>
                    <div id="noNavLeftLine"></div>
                    <div id="noNavTitle">@yield('page-title')</div>
                    <div id="noNavRightLine"></div>
                </div>
            </div>
            <div class='row'>
                @yield('content')
            </div>
        </div>
        
        <div class='row' id="footer">
            @section('footer')
                Copyright &copy; 2013 - {{ date('Y') }} White Knight Safety Solutions Inc. All Rights Reserved.
            @show
        </div>
    </div>
@stop
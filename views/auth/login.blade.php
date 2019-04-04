@extends('webApp::layouts.noNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/auth/auth.css') }}}" rel="stylesheet">
@stop

@section('page-title')
    Account Login
@stop

@section('content')
    <div class='row'>
        <div class='col-md-4'></div>
        <div class='col-md-4'>
            <br/>
            {{ Form::open(array('url'=>'login')) }}
                <div class="input-prepend">
                    <span class="prepend-add-on add-on-email"> </span> <span>{{ Form::text('email','',array('placeholder'=>'Email')) }}</span>
                </div>
                <br/>
                <br/>
                <div class="input-prepend">
                    <!--<span class="prepend-add-on add-on-password"> </span>--> {{ Form::password('password',array('placeholder'=>'Password')) }}
                </div>
                <br/>
                <br/>
                @if (Session::get('error'))
                    <div style="color:red"> {{ Session::get('error') }}</div>
                    <br/>
                @endif
                @if (Session::get('message'))
                    <div style="color:green"> {{ Session::get('message') }}</div>
                    <br/>
                @endif
                {{ Form::text('tz_offset','',array('style'=>'display:none!important','id'=>'tz_offset')) }}
                {{ Form::submit('Login',array('class'=>'btn-orange')) }}
            {{ Form::close() }}
            <a class ="simpleLink" href ="<?php echo URL::to('forgot-password')?>"> Forgot your password? </a>
        </div>
        <div class='col-md-4'></div>
    </div>
@stop

@section('footer')
    @parent
@stop


@section('scripts')
    @parent
     <script>
        $(document).ready(function(){
            var date = new Date();
            var offset = date.getTimezoneOffset()*60;
            $("#tz_offset").val(String(offset));
        });
    </script>
@stop
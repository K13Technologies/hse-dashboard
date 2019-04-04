@extends('webApp::layouts.noNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/auth/auth.css') }}}" rel="stylesheet">
@stop

@section('page-title')
    Password Recovery
@stop

@section('content')
    @if (Session::get('error'))
        <div style="color:red" class="fixed-position-error"> {{ Session::get('error') }}</div>
    @endif
    @if (Session::get('message'))
        <div style="color:green" class="fixed-position-error"> {{ Session::get('message') }}</div>
    @endif
    {{ Form::open(array('url'=>'forgot-password')) }}
     <div class="input-prepend">
        <span class="prepend-add-on add-on-email"> </span>
        {{ Form::text('email','',array('placeholder'=>'Email')) }}
    </div>
    <br/>
    <br/>
    {{ Form::submit('Reset your password',array('class'=>'btn-orange')) }}
    <br/>
    {{ Form::close() }}

@stop

@section('footer')
    @parent
@stop
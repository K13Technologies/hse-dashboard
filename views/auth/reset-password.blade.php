@extends('webApp::layouts.noNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/auth/auth.css') }}}" rel="stylesheet">
@stop

@section('page-title')
    New Password
@stop

@section('content')
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    {{ Form::open(array('url'=>"reset-password/{$resetToken}")) }}
    <div class="input-prepend">
        <span class="prepend-add-on add-on-password"> </span>
        {{ Form::password('password',array('placeholder'=>'New Password')) }}
    </div>
    <br/>
    <br/>
    <div class="input-prepend">
        <span class="prepend-add-on add-on-retype-password"> </span>
        {{ Form::password('confirm',array('placeholder'=>'Confirm Password')) }}
    </div>
    <br/>
    <br/>
    {{ Form::submit('Reset your Password',array('class'=>'btn-orange')) }}
    <br/>
    {{ Form::close() }}

@stop

@section('footer')
    @parent
@stop
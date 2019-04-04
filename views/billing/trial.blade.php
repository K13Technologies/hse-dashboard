@extends('webApp::layouts.noNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/auth/auth.css') }}}" rel="stylesheet">
@stop

@section('page-title')
    Start Your 30 Day Free Trial
@stop

@section('content')

    @if (Session::get('error'))
        <div style="color:red" class="fixed-position-error"> {{ Session::get('error') }}</div>
    @endif

    {{ Form::open(array('url'=>'free-trial')) }}
    <div class="input-prepend">
        <span class="prepend-add-on add-on-email"> </span>
        {{ Form::text('email','',array('placeholder'=>'Email')) }}
    </div>
    <br/>
    <br/>
    <div class="input-prepend">
        <span class="prepend-add-on add-on-company"> </span>
        {{ Form::text('company','',array('placeholder'=>'Company')) }}
    </div>
    <br/>
    <br/> 
    {{ Form::submit('Start your 30 day free trial',array('class'=>'btn-orange')) }}
    {{ Form::close() }}

@stop

@section('footer')
    @parent
    
@stop

@section('scripts')
    @parent
@stop
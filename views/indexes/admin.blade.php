@extends('webApp::layouts.withNav')
@section('styles')
     @parent
@stop

@section('content')
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif
    
    <h4>Admin dashboard page</h4>
@stop


@section('scripts')
  @parent 
@stop
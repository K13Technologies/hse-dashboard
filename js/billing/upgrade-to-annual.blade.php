@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/billing/details.css') }}}" rel="stylesheet">
@stop

@section('content')

<div class='container'>
    <h1> Hello, {{$company->company_name }}! </h1>
    
    <div class="row">
    <input type='hidden' value='{{$company->company_id}}' id='companyId'/>
    <div class="span6">
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component">
                        We've made some changes.
                        @endif
                    </span>
                </div>
            </div>
            <div class="list-container-body">
                <h3>
                    With all of the new features we have added, the current monthly plan you're on has been discontinued and replaced by a simpler pricing structure
                    of $2500 per year, plus GST. Please click below to upgrade your subscription!
                </h3>
                <button class='btn btn-success btn-lg'>Upgrade </button>

                Your charge will be prorated for however much you have been invoiced this much.           
            </div>
        </div>
    </div>
</div>
@stop


@section('scripts')
  @parent 
    <script src="{{{ asset('assets/js/wkss/billing/details.js') }}}"></script>
@stop
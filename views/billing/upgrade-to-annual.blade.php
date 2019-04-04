@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/billing/details.css') }}}" rel="stylesheet">
@stop

@section('content')

<div class='container'>
    <h1> Hello, {{Auth::user()->first_name . " " . Auth::user()->last_name }}! </h1>
    <br/>
    <div class="row">
    <input type='hidden' value='{{$company->company_id}}' id='companyId'/>
    <div class="span6">
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component">
                        We've made some changes.
                    </span>
                </div>
            </div>
            <div class="list-container-body">
                <p>
                    <h4>
                        With all of the new features we have added, the current monthly plan you're on has been discontinued and replaced by a simpler pricing structure
                        of $2500 per year, plus GST. Please click below to update your credit card information and to upgrade your subscription!
                    </h4>
                </p>
                <p>
                    <h4> 
                        You will be given a 30 day free trial before you are charged, and your charge will be prorated if you have paid for service this month.
                    </h4>
                </p>

                <br/>
                <div class='text-center'>
                    <a href="{{ URL::to('/billing/credit-card-details') }}">
                        <button class='btn btn-success btn-lg'><b>Upgrade</b></button>
                    </a>
                </div>         
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
  @parent 
    <script src="{{{ asset('assets/js/wkss/billing/details.js') }}}"></script>
@stop
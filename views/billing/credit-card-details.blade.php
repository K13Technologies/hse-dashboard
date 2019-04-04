@extends('webApp::layouts.withNav')
@section('styles')
    @parent
    <link href="{{{ asset('assets/css/wkss/billing/details.css') }}}" rel="stylesheet">
    <meta name='publishable-key' content='{{ Config::get("stripe.stripe.public") }}'>
    <style>
        .payment-errors {
            padding-bottom: 30px;
        }
    </style>
@stop

@section('content')

<div class='container'>
    <div class='row'>
        <div class='col-md-12'>
            <br/>
            <br/>
            <div class="form-container">
                <div id="card-form-logo">
                    <img src="{{ asset('assets/img/wkss/logo_login.png') }}" id="card-form-logo-img"/>
                </div>
                <br/>
                <br/>
                <h4 class='separator' id='cc-title'> 
                    @if($company->everSubscribed())
                        Modify your credit card details
                    @else
                        Please enter your credit card details
                    @endif
                </h4>
                <form action="" method="POST" id="payment-form" class="form-horizontal separator">
                    <b><div class="payment-errors"> </div></b>
                    <div class="control-group">
                        @if($company->everSubscribed())
                            {{ Form::label('', 'Current Plan', array('class'=>'control-label')) }} 
                            <ul>
                                <li>{{ $company->stripe_plan }}</li>
                            </ul>
                        @else
                            {{ Form::label('', 'Choose a Plan', array('class'=>'control-label')) }} 
                            {{Form::select('stripe_plan', $plans, NULL , array('class'=>'form-control', 'required'))}}
                        @endif
                        
                        {{ Form::label('', 'Credit Card Number', array('class'=>'control-label')) }}
                        <div class="controls">
                            <input type="text" size="20" data-stripe="number" placeholder="XXXX XXXX XXXX XXXX" id="credit-card-input" class='form-control required'/>
                            <span class="help-block"><img src="{{ asset('assets/img/wkss/credit_card_icons_scaled.png') }}" title="We accept Visa, Master Card and American Express"/></span>
                        </div>
                    </div>  
                    <div class="control-group">
                        {{ Form::label('', 'Security Code CVC', array('class'=>'control-label')) }} <span class="help-inline"> (The 3 digits on the back of the credit card) </span>
                        <div class="controls">
                            <input type="text" maxLength='3' data-stripe="cvc" id="cvc-input" class='form-control'/> 
                        </div>
                    </div> 

                    <div class="control-group">
                        <div class="controls">
                            {{ Form::label('', 'Expiration Month', array('class'=>'control-label')) }}
                            <select data-stripe="exp-month" id="cc-month" class='form-control'>
                                <option value="" selected="selected"> - </option>
                                @for($i=1;$i<=12;$i++)
                                    <option value="{{ $i }}"> {{ $i}} ({{ date('M',strtotime("2014-".$i."-01")) }}) </option>
                                @endfor
                            </select>

                            {{ Form::label('', 'Expiration Year', array('class'=>'control-label')) }}                            
                            <select data-stripe="exp-year" id="cc-year" class='form-control'>
                                <option value="" selected="selected"> - </option>
                                <?php $year = date('Y'); ?>
                                @for($i=$year;$i<=$year+20;$i++)
                                    <option value="{{ $i }}"> {{ $i }} </option>
                                @endfor
                            </select>
                        </div>
                    </div>  

                    @if(!$company->everSubscribed() || $company->stripe_plan == BillingController::TIER_1_PRICING)
                        <div class='control-group'>
                            {{ Form::label('', 'Coupon', array('class'=>'control-label')) }}  
                            <input type="text" class='form-control' name='coupon_code' id='checkableCouponCode'/> 
                        </div>
                    @endif
                    <br/>

                    <div style="text-align: center">
                        <p>
                            @if($company->everSubscribed())
                                @if($company->stripe_plan == BillingController::TIER_1_PRICING)
                                    Since you are switching to the annual plan, your card will be charged in 30 days (charge will be prorated).
                                @else
                                    You will not be charged until your next billing cycle.
                                @endif
                            @else
                                @if( Carbon\Carbon::createFromTimestamp(strtotime($company->trial_ends_at))->isFuture() )
                                    You will not be charged until your free trial is over.
                                @else
                                    Your free trial has run out, so you will be charged immediately.
                                @endif
                            @endif
                        </p>
                        <button type="submit" class="btn btn-success btn-md">Submit Credit Card Details</button>
                        <br/>
                        <br/>
                    </div>
                </form>
                
                <div style='padding:10px 20px;font-size:12px;'>
                    <p>
                        <b>Disclaimer: </b> White Knight Safety doesn't save any of your credit card details.
                        All credit card information is held and processed by <a href="https://stripe.com">Stripe billing service</a>.
                    </p>
                    <div class="center">
                        <a href="https://stripe.com" class="export-button"><img src="{{ asset('assets/img/wkss/stripe_solid_badge_big.png') }}" title="Powered by Stripe"/></a>
                        <a href="https://www.geotrust.com/"><img src="{{ asset('assets/img/wkss/geotrust-logo.jpg') }}" title="Secured by GeoTrust"/></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('scripts')
  @parent 
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script src="{{{ asset('assets/js/jquery.payment.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/billing/card-billing.js') }}}"></script>
@stop
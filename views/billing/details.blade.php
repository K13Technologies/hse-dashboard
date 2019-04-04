@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/wkss/billing/details.css') }}}" rel="stylesheet">
@stop

@section('content')

<!-- START MODALS !-->
<div id="modalSubscriptionCancel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalCancel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
              <h4 id="modalCancel"> Cancel the subscription for {{$company->company_name}}</h4>
            </div>
            <div class="modal-body">
                {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
                <h4> Are you sure you want to cancel the subscription for this company?</h4>
                <h5> If you do, your company will be marked for deactivation and your company admins will not be able to login in the dashboard. </h5>
                <h5> Your company will be on grace period until the next billing cycle would have occurred. </h5>
                <h5> Please keep in mind that you can resume your subscription at any time.</h5>
            </div>
            <div class="modal-footer">
                <button class="btn-grey pull-left" data-dismiss="modal" aria-hidden="true">EXIT</button>
                {{ Form::button('CANCEL SUBSCRIPTION',array('class'=>'btn-orange medium pull-right','id'=>'cancelSubscription')) }}
                <span id='mailError' style="color:red"> </span>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
<!-- END MODALS !-->

<div class='container'>

    <h3> Billing Details for {{$company->company_name }} </h3>
    
    <div class="row">
    <input type='hidden' value='{{$company->company_id}}' id='companyId'/>
    <div class="span6">
        <div class="list-container">
            <div class="list-container-header">
                <div class="list-component-container">
                    <span class="list-component">
                        Status: 
                        @if ($company->everSubscribed())
                        <span class="{{!$company->cancelled()?"status-ok":"status-cancelled"}} "> 
                            {{!$company->cancelled()?"Active":"Inactive"}} 
                        </span>
                        @else 
                        <span class="status-pending"> 
                            Not Subscribed
                        </span>
                        @endif
                    </span>
                </div>
                @if (!$company->everSubscribed())
                    <span class="pull-right">
                        <a href="{{ URL::to('billing/credit-card-details') }}">
                            <button class="btn btn-warning">
                                <i class="icon-white icon-shopping-cart"></i>
                                <b>Subscribe</b>
                            </button>
                        </a>
                    </span>
                @else 
                    @if(!$company->cancelled())
                        <span class="pull-right">
                            <a href="#modalSubscriptionCancel" data-toggle="modal">
                                <button class="btn btn-danger">
                                    <i class="icon-white icon-ban-circle"></i>
                                     Cancel Subscription
                                </button>
                            </a>
                        </span>
                    @else
                        <span class="pull-right">
                            <button class="btn btn-success" id="resumeSubscription">
                                Resume Subscription
                                <i class="icon-white icon-arrow-right"></i>
                            </button>
                        </span>
                    @endif
                @endif
            </div>
            <div class="list-container-body">
                @if ($company->everSubscribed())
                    <table class="table-list">
                        <tbody>
                            <tr>
                                <td> Detailed status </td>
                                <td class="right">
                                    <b>
                                    @if($company->onTrial())
                                        @if($company->onGracePeriod())
                                            <span class="status-pending">On trial grace period  <br/> until {{ WKSSDate::display(strtotime($company->subscription_ends_at), $company->subscription_ends_at) }}</span>
                                        @else
                                            <span class="status-ok">On trial period <br/> until {{ WKSSDate::display(strtotime($company->trial_ends_at), $company->trial_ends_at) }}</span>
                                        @endif
                                    @elseif($company->onGracePeriod())
                                        <span class="status-pending">On paid subscription grace period <br/>until {{ WKSSDate::display(strtotime($company->subscription_ends_at), $company->subscription_ends_at) }}</span>
                                    @elseif($company->cancelled())
                                        <span class="status-cancelled"> Cancelled </span>
                                    @else
                                        <span class="status-ok"> Paid subscription </span>
                                    @endif
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td> Current plan </td>
                                <td class="content-description">
                                    {{ $company->stripe_plan }} (${{$pricing}})
                                    
                                    @if(!$company->hasFullServicePlan())
                                        &nbsp;&nbsp;&nbsp;
                                        <a class='btn btn-md btn-warning' href="{{ URL::to('purchase-services') }}">
                                            Upgrades Available!
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Current subscription coupon
                                </td>
                                <td class="content-description">
                                    <?php 
                                        $customer = $company->subscription()->getStripeCustomer();
                                        $coupon = NULL;
                                        // if(isset($customer->discount)) {
                                        //    $coupon = $customer->discount->coupon;  
                                        // }
                                        if($customer->subscription->discount) {
                                            $coupon = $customer->subscription->discount->coupon;
                                        }
                                    ?>
                                    <div class="row">
                                        <div class="col-md-3">
                                            {{ $coupon ? $coupon->id : "None" }}
                                        </div>
                                        <div class="col-md-3">
                                            @if($coupon)
                                                <button id='removeCouponBtn' class="btn btn-md btn-danger">Remove</button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @if($company->subscription())
                                @if(!$coupon)
                                    <tr>    
                                        <td> Apply coupon to subscription </td>
                                        <td class="content-description">
                                            {{ Form::open(array('class'=>'form-horizontal','id'=>'couponForm')) }}
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <input type="text" class='form-control' name='coupon_code' id='subscriptionCoupon'/> 
                                                        <input type="text" data-stripe="token" hidden/> 
                                                    </div>
                                                    <div class="col-md-3">
                                                        {{ Form::button('Apply',array('class'=>'btn btn-md btn-primary','id'=>'validateCouponBtn')) }}
                                                    </div>
                                                </div>
                                            {{ Form::close() }}
                                        </td>
                                    </tr>
                                @else 
                                    <tr>    
                                        <td> Modify coupon </td>
                                        <td class="content-description">
                                            {{ Form::open(array('class'=>'form-horizontal','id'=>'couponForm')) }}
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <input type="text" class='form-control' name='coupon_code' id='subscriptionCouponCode'/> 
                                                        <input type="text" data-stripe="token" hidden/> 
                                                    </div>
                                                    <div class="col-md-3">
                                                        {{ Form::button('Apply',array('class'=>'btn btn-primary','id'=>'validateCouponBtn')) }}
                                                    </div>
                                                </div>
                                            {{ Form::close() }}
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                @else 
                    <h4 class='text-center'>Your free trial has run out! Please subscribe to continue using White Knight Safety.</h4>
                @endif
            </div>
        </div>
    </div>
    
    <div class="span6">
        @if ($company->everSubscribed())
            <div class="list-container">
                <div class="list-container-header">
                    <div class="list-component-container">
                        <span class="list-component">
                            Credit card details
                        </span>
                    </div>
                    <span class="pull-right">
                        <a href="{{ URL::to('billing/credit-card-details') }}">
                            <button class="btn btn-info">
                                <i class="icon-white icon-refresh"></i>
                                Modify Card
                            </button>
                        </a>
                    </span>
                </div>
                <div class="list-container-body">
                    <table class="table-list">
                        <tbody>
                            <tr>
                                <td> Current credit card</td>
                                <td class="right">
                                    <b>
                                        XXXX-XXXX-XXXX-{{ $company->last_four }}
                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{--
            <div class="list-container">
                <div class="list-container-header">
                    <div class="list-component-container">
                        <span class="list-component">
                            Subscription Invoices
                        </span>
                    </div>
                </div>
                <div class="list-container-body">
                    <table class="table-list">
                        <thead>
                            <tr>
                                <th> Billing date </th>
                                <th> Amount </th>
                                <th>  </th>
                            </tr>
                        </thead>
                        <tbody> 
                            <!-- allInvoices(true) means to include pending items -->
                            @foreach ($company->subscription()->allInvoices(true) as $invoice)
                                @if($invoice->amount_due != 0)
                                    <tr>
                                        <td> {{ WKSSDate::display($invoice->period_start,'') }} </td>
                                        <td>
                                            <b>
                                               {{ $invoice->totalWithCurrency() }}
                                            </b>
                                        </td>
                                        <td class="pull-right">
                                            <a href="{{ URL::to('billing/invoice/export', $invoice->id) }}" title="Download">
                                                <button class="btn btn-primary btn-md">
                                                    Download &nbsp;
                                                    <i class="glyphicon glyphicon-white glyphicon-download-alt"></i>
                                                </button>
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach 
                        </tbody>
                    </table>
                </div>
            </div>
            --}}
        @endif
    </div>
</div>
</div>
@stop


@section('scripts')
  @parent 
    <script src="{{{ asset('assets/js/wkss/billing/details.js') }}}"></script>
@stop
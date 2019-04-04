@extends('webApp::layouts.withNav')
@section('styles')
    @parent
    <link href="{{{ asset('assets/css/wkss/billing/details.css') }}}" rel="stylesheet">
    <link href="{{{ asset('assets/css/jquery/jquery.webui-popover.min.css') }}}" rel="stylesheet">
@stop

@section('content')
<!-- MODALS -->
<div class="modal" id="howDoesItWork">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="modalEmailExport">Business Service Process</h4> 
        </div>
        <div class="modal-body">
            <h4>How does it work?</h4>
            <ol>
                <li>Click on the purchase button. </li>
                <li>Fill out required information in the popup.</li>
                <li>We will contact you the following business day. </li>
                <li>Once we confirm your requirements via phone we will ask you to securely upload your documents to us via Box.</li>
                <li>We will process your documentation and let you know via phone and or email once we have completed your request.</li>
            </ol>
            
            If you have any further questions please email us at <a href="mailto:sales@whiteknightsafety.com">sales@whiteknightsafety.com</a> or contact us at <b>403-477-2318</b>.
        </div>
        <div class="modal-footer">
            <button class='btn btn-md btn-primary pull-right' data-dismiss="modal">OK</button>
        </div>
    </div>
  </div>
</div>

<div class="modal" id="moreInfoPurchase">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 id="modalEmailExport">Enter Purchase Details</h4> 
        </div>
        <div class="modal-body">
            <h4>Before you confirm your purchase, we just need a bit more information from you!</h4>
            {{ Form::open(array('class'=>'form-horizontal','id'=>'purchaseInfoForm')) }}
                {{ Form::hidden('service_code', null, array('class'=>'hiddenServiceIdentifier')) }}

                {{ Form::label('phone', 'What is your preferred phone number where we can reach you?', []) }}
                {{ Form::text('phone', null, ['class'=>'form-control']) }}
                <br/>
                Do you require any other consulting services? If so, please let us know in the comments below. Some of our consulting services include:
                <br/>
                <br/>
                <ul>
                    <li>Field incident investigations </li>
                    <li>Perform a GAP Audit on safety manual</li>
                    <li>Site specific safety plans (ERP)</li>
                    <li>Customize safety manual</li>
                    <li>Field in-house safety advisors </li>
                </ul>

                {{ Form::label('comments', 'Comments:', []) }}
                {{ Form::textarea('comments','',array('placeholder'=>'','rows'=>'3','class'=>'form-control')) }}
            {{ Form::close() }}
        </div>
        <div class="modal-footer">
            <div class='text-right'>
                <!-- data-dismiss="modal" -->
                <button class='btn btn-md btn-success finalPurchaseConfirmationBtn'><b>CONFIRM PURCHASE</b></button>
                <br/>
                <br/>
                <i>We will follow up with you within one business day</i>
            </div>
        </div>
    </div>
  </div>
</div>


<!-- END MODALS --> 

<div class='container'>
    <div class="col-md-12 well well-sm whiteBackground">
        <div class='text-center'>
            <h1 style='margin-top: 0px;'><b>Business Services</b> </h1>
            White Knight Safety offers complete, smart and simple service solutions for your business. 
            <a href='#howDoesItWork' data-toggle="modal" type="button">How do the services work?</a> 
        </div>
    </div>

    {{ Form::open(array('class'=>'form-horizontal','id'=>'purchaseServiceForm')) }}

    <div class="row">
      <div class="col-md-4">
        <div class="panel panel-info">
         <div class="panel-heading"><h3 class="text-center"><b>Starter</b></h3></div>
         <div class="panel-body text-center">
           <p class="lead" style="font-size:40px; margin-bottom: 0px"><strong>$299</strong></p>
           <p><i>One-time fee plus GST</i></p>
         </div>
         <div class='text-center'>
             <ul class="list-group list-group-flush" style="display: inline-block; text-align: left">
                <li class="list-group-item noBorder">One-time setup 
                    <i class="glyphicon glyphicon-question-sign fullServiceSetup hoverHandLol"></i>
                </li>
                <li class="list-group-item noBorder">Account setup included</li>
                <li class="list-group-item noBorder">Ticket &amp; certificate upload *</li>
                <li class="list-group-item noBorder">Completed in 3 business days </li>
                <li class="list-group-item noBorder">Email &amp; phone support  </li>
             </ul>
        </div>
         <div class="panel-footer text-center">
            <p><i>*Some conditions and restrictions apply </i></p>
            {{ Form::button('<b>BUY NOW</b>', array('class'=>'btn btn-lg btn-block btn-success purchaseServiceBtn', 'value'=>1)) }}
          </div><!--/panel-footer-->
        </div><!--/panel-->
      </div><!--/col-->
      <div class="col-md-4">
        <div class="panel panel-info">
         <div class="panel-heading"><h3 class="text-center"><b>Professional</b></h3></div>
         <div class="panel-body text-center">
            <p class="lead" style="font-size:40px; margin-bottom: 0px"><strong>$349</strong></p>
            <p><i>One-time fee plus GST</i></p>         
         </div>
        <div class='text-center'>
            <ul class="list-group list-group-flush" style="display: inline-block; text-align: left">
                <li class="list-group-item noBorder">
                    One-time setup
                    <i class="glyphicon glyphicon-question-sign fullServiceSetup hoverHandLol"></i>
                </li>
                <li class="list-group-item noBorder">Account setup included</li>
                <li class="list-group-item noBorder">Upload safety manual *</li>
                <li class="list-group-item noBorder">Completed in 3 business days</li>
                <li class="list-group-item noBorder">Email &amp; phone support  </li>
            </ul>
        </div>
         <div class="panel-footer text-center">
            <p><i>*Some conditions and restrictions apply </i> </p>
            {{ Form::button('<b>BUY NOW</b>', array('class'=>'btn btn-lg btn-block btn-success purchaseServiceBtn', 'value'=>2)) }}
          </div><!--/panel-footer-->
        </div><!--/panel-->
      </div><!--/col-->
      <div class="col-md-4">
        <div class="panel panel-info">
         <div class="panel-heading"><h3 class="text-center"><b>Business</b></h3></div>
         <div class="panel-body text-center" style='padding:0px;'>
            <div class='list-group-flush' style='background-color:black; color:white;'>
                MOST POPULAR
            </div> 
            <p class="lead" style="font-size:40px; margin-bottom: 0px"><strong>$549</strong></p>
            <p><i>One-time fee plus GST</i></p>  
            <div class='list-group-flush' style='background-color:#D3D3D3; height:35px; font-size:25px; color: #3c4a5c;'>
                SAVE $100
            </div>       
         </div>
         <div class='text-center'>
             <ul class="list-group list-group-flush" style="display: inline-block; text-align: left">
                <li class="list-group-item noBorder">
                    One-time setup
                    <i class="glyphicon glyphicon-question-sign fullServiceSetup hoverHandLol"></i>
                </li>
                <li class="list-group-item noBorder">Account setup included </li>
                <li class="list-group-item noBorder">Upload safety manual * </li>
                <li class="list-group-item noBorder">Upload tickets and certificates * </li>
                <li class="list-group-item noBorder">Completed in 5 business days  </li>
                <li class="list-group-item noBorder">Email &amp; phone support </li>
             </ul>
        </div>
         <div class="panel-footer text-center">
           <p><i> *Some conditions and restrictions apply</i> </p>
           {{ Form::button('<b>BUY NOW</b>', array('class'=>'btn btn-lg btn-block btn-success purchaseServiceBtn', 'value'=>3)) }}
          </div><!--/panel-footer-->
        </div><!--/panel-->
      </div><!--/col--> 
    </div><!--/row-->

    @if(!$company->hasFullServicePlan())
        <div class="row">
          <div class="col-md-12">
            <div class="panel panel-info">
             <div class="panel-heading"><h3 class="text-center"><b>Performance</b></h3></div>
             <div class="panel-body">
                <div class='text-center bestValueHeader'>BEST VALUE</div>
                <div class='col-md-12'>
                    <div class='col-md-12 text-center'>
                        <br/>
                        <h4><b>We handle the health and safety side so you can concentrate on what's important: building your business.</b></h4>
                        <hr/>
                    </div> 
                    <div class='col-md-4 text-center'>
                        <div class='fullPlanPricePurchaseBlock' style='margin-top:15%;'>
                            <p class="lead" style="font-size:40px; margin-bottom: 0px;"><strong>$625 / month</strong></p>
                            <i>
                                <p>Billed annually plus GST</p>
                                <p>Max 25 users</p>
                            </i>
                            <br/>
                            @if(!$company->stripe_plan)
                                {{ Form::button('<b>SUBSCRIBE TO THIS PLAN</b>', array('class'=>'btn btn-lg btn-block btn-success purchaseServiceBtn', 'value'=>4)) }}
                            @elseif($company->hasFullServicePlan())
                                {{ Form::button('<b>YOU ARE ON THIS PLAN</b>', array('class'=>'btn btn-lg btn-block btn-success disabled')) }} 
                            @else
                                {{ Form::button('<b>UPGRADE NOW</b>', array('class'=>'btn btn-lg btn-block btn-success purchaseServiceBtn', 'value'=>4)) }}
                            @endif
                        </div>
                    </div>
                    <div class='col-md-8' style='font-size:12pt;'>
                        <div class='col-md-6'>
                            Account setup included <br/>
                            New user onboarding <br/><br/>

                            Software included <br/><br/>
                            Provide monthly safety meeting powerpoint <a target="blank" href="{{ URL::to('assets/pdf/sample_safety_meeting.pdf') }}">(download sample)</a> <br/>
                            Request monthly topic <br/><br/>

                            Send reminder notifications regarding internal/external COR dates <br/>
                            Book COR Auditor dates at your request <br/><br/>
                        </div>
                        <div class='col-md-6'>
                            Import current safety manual <br/>
                            Update safety manual revisions <br/>
                            Update revisions within 48 hours of request <br/><br/>

                            Import tickets &amp; certificates <br/>
                            Update tickets &amp; certificates <br/>
                            Send reminder notifications when tickets expire <br/>
                            Provide available training courses times &amp; locations <br/><br/>
                        </div>
                    </div>
                </div>  
             </div>
            </div><!--/panel-->
          </div><!--/col-->
        </row> <!--/row-->
    @endif

    <div class="col-md-12">
        <div class=' well greenBackground text-left' style='color:white;'>
            <h1 style='margin-top: 0px;'>Questions? We have answers.</h1>
            <hr/>
            <b>
                If you have questions about products, implementation, integration or anything else, let us know. <br/><br/>
                We are standing by, ready with answers. <br/><br/>
                <a class='btn btn-lg btn-primary' href="mailto:sales@whiteknightsafety.com"><b>EMAIL US</b></a>
                <br/>
                <br/>
                <p>OR CALL (403) 477-2318</p>
            </b>
        </div>
    </div>

    {{ Form::close() }}
</div> <!--/container-->
@stop

@section('scripts')
  @parent
    <script src="{{{ asset('assets/js/wkss/billing/details.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/billing/purchase-services.js') }}}"></script>
    <script src="{{{ asset('assets/js/jquery/jquery.webui-popover.min.js') }}}"></script>
    <script>
        var hasCC = {{ $company->stripe_active }};
    </script>
@stop
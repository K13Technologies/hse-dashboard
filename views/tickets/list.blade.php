@extends('webApp::layouts.withNav')
@section('styles')
     @parent
     <link href="{{{ asset('assets/css/lightbox.css') }}}" rel="stylesheet">
     <style>
        #addTicketBtn {
            margin-top:10px;
        }
        .workerBox:hover {
            cursor: pointer; 
            cursor: hand;
        }
        #clearSearchBtn {
            margin-top: 20px;
        }

        .alert-expired {
            background-color:#000000;
            border-color:#000000;
            color:white;
        }

        .alertDangerCustom {
            background-color:#db524b;
            border-color:#db524b;
            color:white;
        }

        .alertWarningCustom {
            background-color:#f2ae44;
            border-color:#f2ae44;
            color:white;
        }

        .alertInfoCustom {
            background-color:#56bfe0;
            border-color:#56bfe0;
            color:white;
        }

        .alertSuccessCustom {
            background-color: #58b958;
            border-color: #58b958;
            color:white;
        }

        .legendBox {
            height:50px;
            width: 120px;
            margin-right: 10px;
        }

        .panelSuccessCustom > .panel-heading {
            background-image: none;
            background: #58b958; 
            color: white; 
        }

        .panelSuccessCustom {
            border-color: #58b958;
        }

        .panelInfoCustom > .panel-heading {
            background-image: none;
            background: #56bfe0; 
            color: white; 
        }

        .panelInfoCustom {
            border-color: #56bfe0;
        }

        .panelWarningCustom > .panel-heading {
            background-image: none;
            background: #f2ae44; 
            color: white; 
        }

        .panelWarningCustom {
            border-color: #f2ae44;
        }

        .panelDangerCustom > .panel-heading {
            background-image: none;
            background: #db524b; 
            color: white; 
        }

        .panelDangerCustom {
            border-color: #db524b;
        }

        .panel-expired > .panel-heading {
            background-image: none;
            background: #000000; 
            color: white; 
        }

        .panel-expired {
            border-color: #000000;
        }

        .contractedTicket {
            height:115px;
        }

        .ticketDescriptionText { 
            /*This wraps text even if it is one monolithic block -- in the rare case that a user might do this*/
            word-wrap: break-word; 
        }

        .dangerText {
            color: #D73530;
        }

        .clickableDescription:hover {
            cursor: pointer; 
            cursor: hand;
        }
     </style>
@stop

@section('content')

<!--=============== START MODALS ===============-->
<div class="modal" id="sendEmailModal">
  <div class="modal-dialog">
    <div class="modal-content">
        {{ Form::open(array('class'=>'form-horizontal','id'=>'mailExportForm')) }}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 id="modalEmailExport"> Email Ticket </h4> 
            </div>
            <div class="modal-body">
                <h5> Please enter an email address (multiple separated by commas):</h5>
                <input type='text' class='hide' name='ticketId'/>
                {{ Form::textarea('email','',array('placeholder'=>'email@address.com','class'=>'form-control','id'=>'email')) }}
            </div>
            <div class="modal-footer">
                <button class="btn btn-grey pull-left" data-dismiss="modal" aria-hidden="true">Cancel</button>
                {{ Form::button('Send',array('class'=>'btn btn-orange medium pull-right','id'=>'completeExportToMail')) }}
                <span id='mailError' style="color:red"> </span>
            </div>
        {{ Form::close() }}
    </div>
  </div>
</div>
<!--=============== END MODALS ===============-->
  
<div class='container'>
    @if (Session::get('error'))
        <div style="color:red"> {{ Session::get('error') }}</div><br/>
    @endif
    @if (Session::get('message'))
        <div style="color:green"> {{ Session::get('message') }}</div><br/>
    @endif

    <div class='row well whiteBackground'>
        <div class='col-md-12'>

            <div class='section-header-label'>Training Matrix</div>
            <br/>
            <div class='col-md-12'>
                <a href="{{ URL::to('tickets/create-ticket-view') }}" class='btn btn-primary' id='addTicketBtn'>Add Ticket</a>
                <br/><br/>
            </div>

            <div class='col-md-12'>
                <div class="alert alert-success alertSuccessCustom col-md-2 legendBox text-center" role="alert">
                    Over 60 Days
                </div>
                <div class="alert alert-info alertInfoCustom col-md-2 legendBox text-center" role="alert">
                    Under 60 Days
                </div>
                <div class="alert alert-warning alertWarningCustom col-md-2 legendBox text-center" role="alert">
                    Under 30 Days
                </div>
                <div class="alert alert-danger alertDangerCustom col-md-2 legendBox text-center" role="alert">
                    Under 14 Days
                </div>
                <div class="alert alert-danger alert-expired col-md-2 legendBox text-center" role="alert">
                    Expired
                </div>
            </div>
        </div>
    </div>

    <div class='row well whiteBackground'>
        <div class='col-md-12'>
            <div class='section-header-label'>Worker Search:</div>
            <br/>
            <div class='col-md-2'>
                Last name starts with:
                <input data-bind="value: lastNameSearch, valueUpdate: 'afterkeydown'" class='form-control'/>
            </div>
            <div class='col-md-2'>
                First name starts with:
                <input data-bind="value: firstNameSearch, valueUpdate: 'afterkeydown'" class='form-control'/>
            </div>
            <div class='col-md-2'>
                <button data-bind="click: clearSearchFields" class='btn btn-primary' id='clearSearchBtn'>Clear</button>
            </div>

        </div>
    </div>


    <!-- ko foreach: filteredWorkers -->
        <div class='row'>
            <div class='col-md-2 well whiteBackground workerBox' data-bind='click: changeTicketsVisibility'>
                <!-- ko if: last_name() != "" && first_name() != "" -->
                    <div class='list-component' data-bind='text: last_name() + ", " + first_name() '></div>
                <!-- /ko -->
                <!-- ko if: last_name() == "" || first_name() == "" -->
                    <div class='list-component' data-bind='text: auth_token() + " (Name not yet filled)" '></div>
                <!-- /ko -->

                <img height=50 width=50 alt="..." class="img-thumbnail" data-bind='attr: {src: site + "image/worker/profile_thumb/" + auth_token()}' />
                <span class="badge" data-bind='text: tickets().length'></span>
            </div>

            <div class='col-md-10 well whiteBackground' data-bind='fadeVisible: ticketsAreVisible'>
                <div data-bind='if: tickets().length < 1'>This worker has no tickets</div>
                <!-- ko foreach: tickets -->
                    <div data-bind="css: {'ticketBox': true, 'col-md-4': !ticketDetailsAreVisible(), 'col-md-12': ticketDetailsAreVisible() }">
                        <div data-bind='css: ticketPanelClasses()'>
                            <div class="panel-heading">
                                <h3 class="panel-title"><strong data-bind='text: lengthRestrictedTicketName()'></strong></h3>
                            </div>
                            <div class="panel-body">
                                <div data-bind='if: ticketDetailsAreVisible'>
                                    <!-- ko fadeIn: true -->
                                        <p><strong class='content-label'>Expiration:  </strong>  <span data-bind='text: $root.readableDate(expiry_date())'></span></p>

                                        <!-- ko if: expiresInThisManyDays() > 0 -->
                                            <p>
                                                <span><strong class='content-label'>Expires in (days):  </strong></span>
                                                <span data-bind='text: expiresInThisManyDays()'></span>
                                            </p>
                                        <!-- /ko -->

                                        <!-- ko if: expiresInThisManyDays() == 0 -->
                                            <p><span><strong class='content-label dangerText'>EXPIRES TONIGHT</strong></span></p>
                                        <!-- /ko --> 

                                        <!-- ko if: expiresInThisManyDays() < 0 -->
                                            <p><span><strong class='content-label dangerText'>TICKET EXPIRED</strong></span></p>
                                        <!-- /ko --> 

                                        <div data-bind='if: photos().length > 0'>
                                            <div class='well'>
                                                <div data-bind='foreach: photos'>
                                                    <a data-bind='attr: { href: photoURL(), "data-lightbox": "ticket" + $parent.ticket_id() + "_photos_" + $index() }'> 
                                                        <img class='img-thumbnail' data-bind='attr: { src: photoURL()}' style='width: 100px; height: 75px;'/>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <p data-bind='if: issued_internally() == 0'>
                                            <strong class='content-label'>Issuer:</strong>  
                                            <span data-bind='text: issuer_organization_name'></span>
                                        </p>
                                        <p data-bind='if: issued_internally() == 1'>
                                            <strong class='content-label'>Internally Issued</strong>  
                                        </p>

                                        <div data-bind='if: managementReview'>
                                            <p><strong class='content-label'>Approved by:   </strong> <span data-bind='text: managementReview.reviewer_name'></span></p>
                                            <img class='admin-signature-image' data-bind='attr: { src: managementReview.signatureURL()}'/>
                                            <p><strong class='content-label'>Date Approved:   </strong> <span data-bind='text: $root.readableDate(managementReview.created_at())'></span></p>
                                        </div>
                                        <!-- ko if: description().length == 0 -->
                                            <div>
                                                <p><b class='content-label'>Description:  </b>  Not Applicable
                                                </p>
                                                <br/>
                                            </div>
                                        <!-- /ko -->
                                        <!-- ko if: description().length > 0 && description().length <= 100 -->
                                            <div>
                                                <p>
                                                    <b class='content-label' data-bind='click: showTicketDescription'>Description:  </b>  <span data-bind='text: description, click: showTicketDescription' class='ticketDescriptionText'></span>
                                                </p>
                                                <br/>
                                            </div>
                                        <!-- /ko -->
                                        <!-- ko if: description().length > 100 -->
                                            <div>
                                                <p>
                                                    <b class='content-label clickableDescription' data-bind='click: showTicketDescription'>Description (Click to Show):  </b>  
                                                    <!-- ko if: ticketDescriptionIsVisible -->
                                                        <span data-bind='text: description, click: showTicketDescription' class='ticketDescriptionText'></span>
                                                    <!-- /ko -->
                                                </p>
                                                <br/>
                                            </div>
                                        <!-- /ko -->
                                    <!-- /ko -->         
                                </div>
                                <button class='btn btn-sm btn-info' data-bind='click: showTicketDetails, text: infoButtonText()'></button>
                                <div class="btn-group">
                                  <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu">
                                    <li><a href="#sendEmailModal" data-toggle="modal" type='button' data-bind='attr: {"data-ticket-id": ticket_id}'>Send Ticket via Email</a></li>
                                    <li><a data-bind='attr: {href: site + "tickets/export/" + ticket_id() }'> Export Ticket to PDF</a></li>
                                    <li><a data-bind='attr: {href: site + "tickets/view/" + ticket_id() }'> Edit Ticket</a></li>
                                    <li><a href="#/" data-bind='click: $parent.deleteTicket'>Delete Ticket</a></li>
                                  </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <!-- /ko -->
            </div>
        </div>
    <!-- /ko -->

</div>       
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/lightbox-2.6.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/training-matrix.js') }}}"></script>
    <script>
        var workers = {{ json_encode($companyWorkers) }};
    </script>    
@stop
@extends('webApp::layouts.master')
@section('bodyStyle')

@stop

@section('styles')
     @parent
     <script type="text/javascript">
          var displayTutorial = {{ Auth::user()->shouldDisplayTutorial() }}; 
          var displayProfile = {{ Auth::user()->shouldDisplayProfile() }}; 
     </script>
     <link href="{{{ asset('assets/css/wkss/withNav.css') }}}" rel="stylesheet">
     <link href="{{{ asset('assets/css/wkss/tutorial.css') }}}" rel="stylesheet">
@stop

@section('body')
    @section('header')
        <nav class="navbar navbar-default">
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="container-fluid">
                  <div class="row">
                    <div class="col-md-1">
                        <a href="{{ URL::to('/') }}" class="navbar-brand">  
                            @if (!Auth::user()->isAdmin() && Auth::user()->company->logo())
                                <img src="{{ URL::to('company-logo') }}" id="navLogo"/> 
                            @else
                                <img src="{{ asset('assets/img/wkss/logo_webadmin.png') }}" id="navLogo"/> 
                            @endif
                        </a>
                    </div>
                    <div class="col-md-11">
                        <ul class="nav navbar-nav navbar-right">
                          <li>
                            <a href="{{ URL::to('purchase-services') }}">
                              <span class="label label-warning purchase-label" style='font-size: 10pt;'>Purchase Services</span>
                            </a>
                          </li>
                          <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                  Welcome, {{ Auth::user()->first_name." ".Auth::user()->last_name }} | {{ Auth::user()->isAdmin() ? "(SUPER USER)" : "" }}
                                      @if (!Auth::user()->isAdmin() && !Auth::user()->company->stripe_active)
                                          <?php 
                                            $daysLeft = Auth::user()->company->trialDaysLeft(); 
                                            $graceDaysLeft = Auth::user()->company->gracePeriodDaysLeft(); 
                                            if($daysLeft >15){
                                                $classColor = 'trial-status-ok';
                                            }elseif($daysLeft >5){
                                                $classColor = 'trial-status-yellow';
                                            }else{
                                                $classColor = 'trial-status-warning';
                                            }
                                            if($graceDaysLeft >15){
                                                $classColorGrace = 'trial-status-ok';
                                            }elseif($daysLeft >5){
                                                $classColorGrace = 'trial-status-yellow';
                                            }else{
                                                $classColorGrace = 'trial-status-warning';
                                            }
                                          ?>
                                          @if(Auth::user()->company->onTrial())
                                              <span class="{{ $classColor }}" >
                                                  {{ ($daysLeft>=0)?$daysLeft:0 }}
                                                  <span id="days-remaining">days remaining  </span>
                                              </span>
                                          @elseif(Auth::user()->company->onGracePeriod())
                                              <span class="{{$classColorGrace}}" >
                                                  {{ ($graceDaysLeft>=0)?$graceDaysLeft:0 }}
                                                  <span id="days-remaining">days remaining  
                                                    @if(Auth::user()->company->is_enterprise)
                                                      (Enterprise Edition)
                                                    @endif
                                                  </span>
                                              </span>
                                          @endif
                                      @endif
                                  <span class="caret"></span>
                              </a>

                            <ul class="dropdown-menu">
                              <li><a href="#modalSignatureCanvas" data-toggle="modal" data-target="#modalProfile"> Profile </a></li>
                              <li role="separator" class="divider"></li>
                              <li><a href="{{ URL::to('logout') }}">Logout</a></li>
                            </ul>
                          </li>
                        </ul>

                        <!-- This button only appears when the navbar has been shrunk to a certain size -->
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                          <span class="sr-only">Toggle navigation</span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                          <span class="icon-bar"></span>
                        </button>
                    </div>
                </div>
                
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="row collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                  <div class='container'>
                    <div class='col-md-12'>
                    <ul class="nav navbar-nav" style='margin-left:7%'>
                      @if (!Auth::user()->isAdmin())
                          <li class="">
                              <a href="{{ URL::to('/') }}">Home</a>
                          </li>
                      @endif
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Projects <!--<span class="caret"></span>!--></a>
                        <ul class="dropdown-menu">
                              <li class="">
                                  <a href="{{ URL::to('company-management') }}">Company Management</a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('daily-signin') }}"> Daily Sign In </a>
                              </li>  
                        </ul>
                      </li>

                      @if (!Auth::user()->isAdmin())
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Safety Manual</a>
                          <ul class="dropdown-menu">
                                <li><a href="{{ URL::to('safety-manual') }}">Safety Manual</a></li>
                                <li><a href="{{ URL::to('safety-manual/section/swp') }}">Safe Work Practices</a></li>  
                                <li><a href="{{ URL::to('safety-manual/section/sjp') }}">Safe Job Procedures</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{ URL::to('safety-manual/revisions') }}">Revision List</a></li>
                          </ul>
                        </li>
                      @endif

                      <li class="dropdown">
                          <a href="{{ URL::to('worker-management') }}"> Employees </a>
                      </li>

                      <li>
                          <a href="{{ URL::to('company-management/stats') }}">Statistics</a>
                      </li>
                      
                      <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Safety Categories <!--<span class="caret"></span>!--></a>
                        <ul class="dropdown-menu">
                              <li class="">
                                  <a href="{{ URL::to('near-misses') }}">Near Misses</a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('hazard-cards') }}">Hazard Cards</a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('field-observations') }}">Field Observations</a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('flha') }}"> FLHAs </a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('tailgates') }}"> Tailgates </a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('incident-cards') }}"> Incidents </a>
                              </li>  
                        </ul>
                      </li>

                      @if (!Auth::user()->isAdmin())
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Training Matrix <!--<span class="caret"></span>!--></a>
                          <ul class="dropdown-menu">
                                <li>
                                    <a href="{{ URL::to('tickets') }}">Training Matrix</a>
                                </li>
                                <li>
                                    <a href="{{ URL::to('tickets/create-ticket-view') }}">Add Ticket</a>
                                </li>
                          </ul>
                        </li>
                      @endif

                      <li class="">
                          <a href="{{ URL::to('vehicle-management') }}"> Vehicle Management </a>
                      </li>

                      <li class="">
                          <a href="{{ URL::to('journeys') }}">Journey Management</a>
                      </li>
                      
                      <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Settings <!--<span class="caret"></span>!--></a>
                          <ul class="dropdown-menu">
                              <li class="">
                                  <a href="{{ URL::to('admin-management') }}">Admin Management</a>
                              </li>
                              <li class="">
                                  <a href="{{ URL::to('faq') }}">Frequently Asked Questions</a>
                              </li>

                              @if (Auth::user()->isAdmin())
                                  <li class="">
                                      <a href="{{ URL::to('faq/manage') }}"> Upload FAQ</a>
                                  </li>
                              @else
                                  <li><a href="{{ URL::to('upload-company-logo') }}">Upload Company Logo</a></li>
                              @endif

                              @if (!Auth::user()->isAdmin() && !Auth::user()->company->is_enterprise)
                                  <li><a href="{{ URL::to('billing/details') }}">Billing Details</a></li>
                              @endif
                          </ul>
                      </li>
                    </ul>
                  </div>
                  </div> <!-- container -->
                </div> <!-- row, navbar collapse -->
            </div> <!-- container-fluid -->
        </nav>
    @show


    <!-- MODALS START -->
    
        <div class="modal fade" id="modalProfile" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modalSignatureCanvasTitle">Complete Your Profile</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class='col-md-4'>
                                {{ Form::open(array('class'=>'form-horizontal','id'=>'editProfileForm')) }}
                                    <div class="control-group">
                                        {{ Form::label('profile_email', 'Email', array('class'=>'control-label')) }}
                                        <div class="controls">
                                            {{ Form::text('profile_email',Auth::user()->email,array('readonly'=>'readonly', 'class'=>'form-control')) }}
                                        </div>
                                    </div>  

                                    <div class="control-group">
                                        {{ Form::label('profile_first_name', 'First name', array('class'=>'control-label')) }}
                                        <div class="controls">
                                            {{ Form::text('profile_first_name',Auth::user()->first_name, ['class'=>'form-control']) }}
                                        </div>
                                    </div>  
                                    
                                    <div class="control-group">
                                        {{ Form::label('profile_last_name', 'Last name', array('class'=>'control-label')) }}
                                        <div class="controls">
                                            {{ Form::text('profile_last_name',Auth::user()->last_name, ['class'=>'form-control']) }}
                                        </div>
                                    </div>  
                                
                                    <div class="control-group">
                                        {{ Form::label('profile_phone_number', 'Phone number', array('class'=>'control-label')) }}
                                        <div class="controls">
                                            {{ Form::text('profile_phone_number',Auth::user()->phone_number, ['class'=>'form-control']) }}
                                        </div>
                                    </div>  
                                {{ Form::close() }}
                            </div>

                            <div class="col-md-8">
                                <div id="signature-pad" class="m-signature-pad">
                                    <div class="m-signature-pad--body">
                                        <canvas width="360" height="200" 
                                              @if(!Auth::user()->signature())
                                                    style="background: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==)"
                                              @else
                                                    style="background: url({{ URL::to('image/admin/profile/signature') }})"
                                              @endif
                                              ></canvas>
                                        
                                        <div id="sigPhoto" class="hidden"  style='width:360px;height:200px;border:1px solid #ccc;
                                                @if(!Auth::user()->signature())
                                                    background: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==)"
                                                @else
                                                    background: url({{ URL::to('image/admin/profile/signature') }})
                                                @endif
                                                '></div>
                                        <span id="sigError" style="color:red"></span>
                                    </div>
                                    <br/>
                                    <div class="m-signature-pad--footer">
                                      <div class="description">Please enter your signature in the area above</div>
                                      <button class="button clear btn btn-info small" id="clearSignatureButton" data-action="clear"><b>Clear</b></button>
                                      <button class="button save btn btn-primary small hidden" id="useSignatureButton" data-action="save"><b>Use this signature</b></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-grey medium pull-left" data-dismiss="modal">Close</button>
                        <button id='editProfileButton' class="btn btn-orange medium pull-right">Save Profile</button>
                    </div>
                </div>
            </div>
        </div>

    <!-- MODALS END -->


    <!-- START Content of individual page -->
    <div class='container-fluid'>
      @if (Session::get('error'))
          <div class='col-md-12'>
              <div class="alert alert-danger alert-dismissible text-center" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <b>Error!</b> {{ Session::get('error') }}
              </div>
              <br/>
          </div>
      @endif
      @if (Session::get('message'))
          <div class='col-md-12'>
              <div class="alert alert-success alert-dismissible text-center" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <b>Success!</b> {{ Session::get('message') }}
              </div>
              <br/>
          </div>
      @endif

      @yield('content')
    </div>
    <!-- END Content of individual page -->

    <div class='row' id="footer">
      <div class='col-md-12'>
        @section('footer')
            Copyright &copy; 2013 - {{ date('Y') }} White Knight Safety Solutions Inc. All Rights Reserved.
        @show
      </div>
    </div>
@stop

@section('scripts')
    @parent
    <script src="{{{ asset('assets/js/jquery/jquery.validate.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/multi-email-validation.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/tutorial.js') }}}"></script>
    <script src="{{{ asset('assets/js/signature_pad.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/form-review.js') }}}"></script>
@stop
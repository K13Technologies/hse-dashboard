@extends('webApp::layouts.withNav')
@section('styles')
    @parent
    <link href="{{{ asset('assets/css/themes/default/default.css') }}}" rel="stylesheet">
    <style>
        #workerEngagementChart {
            width: 100% !important; /*This makes it so that it stays within the parent container, but causes lag on page resize*/
            height: 100%;
            padding: 10px;
            border-radius: 10px;

            /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#ffa84c+0,ff7b0d+100;Orange+3D */
            background: #ffa84c; /* Old browsers */
            background: -moz-linear-gradient(top,  #ffa84c 0%, #ff7b0d 100%); /* FF3.6+ */
            background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffa84c), color-stop(100%,#ff7b0d)); /* Chrome,Safari4+ */
            background: -webkit-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* Chrome10+,Safari5.1+ */
            background: -o-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* Opera 11.10+ */
            background: -ms-linear-gradient(top,  #ffa84c 0%,#ff7b0d 100%); /* IE10+ */
            background: linear-gradient(to bottom,  #ffa84c 0%,#ff7b0d 100%); /* W3C */
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffa84c', endColorstr='#ff7b0d',GradientType=0 ); /* IE6-9 */
        }
        #sourceDescription {
            margin-right:10px;
            margin-bottom:-10px;
        }
        .ui-datepicker-inline {
            width: 100%;
            height: 100%;
        }
        #incidentLink {
            text-decoration: none;
        }
        #wholeTreesSavedCount {
            padding-right: 30px;
        }
        #labourCostSavingsContainer, #treesSavedContainer {
            height: 160px;
            min-width: 147px;
        }
        #treesSavedContentContainer {
            font-size: 27pt;
        }
        .infoFeedContainerWithData {
            height: 950px;
        }
        .infoFeedContainerNoData {
            height: 162px;
        }
        .customExpiredLabel {
            background-color:black;
        }
        .customDangerLabel {
        }
        body {
            /* Nice font color for the dashboard */
            color: gray;
        }
    </style>
@stop

@section('content')
    <div class='col-md-3'>
        <div class='well whiteBackground wellNoPadding {{ $expiringTickets ? "infoFeedContainerWithData" : "infoFeedContainerNoData" }}'>
            <div class='headerRoundedBorder greenBackground text-center'>
                <p class='list-component'>Field Form Feed</p>
            </div>
            <br/>
            @if($recentFormActivities != NULL)
                @foreach($recentFormActivities as $recent)  
                    <div class='recentItem'>
                        <b>
                            {{ $recent->added_by->first_name }} {{ $recent->added_by->last_name }}
                        </b>
                        submitted a
                            @if($recent->formTypeName() == 'Near Miss')
                                <a href="{{ URL::to('near-misses/view', array($recent->near_miss_id)) }}">
                            @elseif($recent->formTypeName() == 'Hazard Card')
                                <a href="{{ URL::to('hazard-cards/view', array($recent->hazard_id)) }}">
                            @elseif($recent->formTypeName() == 'Field Observation')
                                <a href="{{ URL::to('field-observations/view', array($recent->positive_observation_id)) }}">
                            @elseif($recent->formTypeName() == 'FLHA')
                                <a href="{{ URL::to('flha/view', array($recent->flha_id)) }}">
                            @elseif($recent->formTypeName() == 'Tailgate')
                                <a href="{{ URL::to('tailgates/view', array($recent->tailgate_id)) }}">
                            @elseif($recent->formTypeName() == 'Incident')
                                <span style='margin-left:-3px'>n</span>
                                <span><a href="{{ URL::to('incident-cards/view', array($recent->incident_id)) }}"><i class="icon-black icon-warning-sign"></i></span>
                            @elseif($recent->formTypeName() == 'Journey')
                                <a href="{{ URL::to('journey-management/view', array($recent->journey_id)) }}">
                            @elseif($recent->formTypeName() == 'Inspection')
                                <span style='margin-left:-3px'>n</span>
                                <a href="{{ URL::to('vehicle-management/view', array($recent->vehicle_id, $recent->inspection_id)) }}">
                            @endif
                            
                            <b>{{ $recent->formTypeName() }}</b>
                            </a>
                            ({{ WKSSDate::display($recent->ts, $recent->created_at) }})
                            <p><img src="{{ URL::to('image/worker/profile_thumb/'. $recent->added_by->auth_token) }}" class="img-thumbnail" alt="Worker Photo" width="50" height="50"></p> 
                    </div>
                    <hr/>
                @endforeach
            @else
                <div class='recentItem text-center'>
                    You have no field form feed activity
                </div>
            @endif
        </div>
    </div>

    <div class='col-md-6'>
        <div class='row'>
            <div class='col-xs-12 col-md-12'>
                <div class='well whiteBackground' id="weatherBox">
                    <div class='list-component text-center'>Weather</div>
                    <!-- Docs at http://http://simpleweatherjs.com -->
                    <div class='row'>
                        <div id="weather" class='col-xs-12 col-md-12'></div>
                    </div>
                    <!-- Currently using HTML5 geolocation to get location -->
                    <!--<button class="js-geolocation btn-sm btn-primary" id='weatherButton'>Use Your Location</button>!-->
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-md-6'>
                <div class='well wellNoPadding whiteBackground' id='treesSavedContainer'>
                    <div class='headerRoundedBorder text-center' style="background-color: #d03262;">
                        <p class='list-component'>Environmental Impact Reduction</p>     
                    </div>
                    <br/>
                    <div id="treesSavedContentContainer" class='text-center'>
                        <span id='wholeTreesSavedCount'>{{ $wholeTreesSavedCount }}</span>
                        <img src="{{URL::to('assets/img/spruce_tree.svg')}}" style="max-height: 85px; max-width:85px;"/>
                        <span>{{ $treesCountPercentage }}% </span>
                    </div>
                    <div class='text-right' id="sourceDescription">* Source: MIT</div>
                </div>
            </div>
            <div class='col-md-6'>
                <div class='well wellNoPadding whiteBackground' id='labourCostSavingsContainer'>
                    <div class='headerRoundedBorder text-center' style='background-color: #FFCC11;'>
                        <p class='list-component'>Total Labour Cost Savings</p>     
                    </div>
                    <br/>
                    <div class='text-center' id="cont">
                        <div id="textContent">${{ Company::getTotalCostSavingsForCompany(Auth::user())}}</div>
                    </div>  
                    <div class='text-right' id="sourceDescription">* Source: PwC</div>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-xs-12 col-md-12'>
                <div class='well whiteBackground'>
                    <p class='list-component text-center'>Worker Engagement</p>
                    <canvas id="workerEngagementChart"></canvas>
                </div>
            </div>
        </div>
        <div class='row'>
            <div class='col-md-4'>
                <a href='{{ URL::to('incident-cards') }}' id="incidentLink">
                    <div class='well redBackground' id="incidentContainer">
                        <div class='text-center list-component'>
                            Incidents (YTD)
                        </div>
                        <h1 class='bigText text-center whiteText'>{{ Incident::getNumberOfIncidentsForCurrentYear(Auth::user()) }}</h1>
                    </div>
                </a>
            </div>
            <div class='col-md-8'>
                <div id="datepicker"></div>
            </div>
        </div>
    </div>

    <div class='col-md-3'>
        <div class='row'>
            <div class='col-md-12'>
                <div class='well whiteBackground wellNoPadding {{ $expiringTickets ? "infoFeedContainerWithData" : "infoFeedContainerNoData" }}'>
                    <div class='headerRoundedBorder greenBackground text-center'>
                        <p class='list-component'>Ticket Feed</p>
                    </div>
                    <br/>
                    @if($expiringTickets)
                        @foreach($expiringTickets as $expiringTicket)  
                            <div class='recentItem'>
                                <i class='glyphicon glyphicon-alert'></i>
                                The ticket <b><a href='{{ URL::to("tickets/view", $expiringTicket->ticket_id) }}'>{{ $expiringTicket->type_name }}</a></b> belonging to

                                <b>{{ $expiringTicket->worker->first_name && $expiringTicket->worker->last_name ? $expiringTicket->worker->first_name . " " . $expiringTicket->worker->last_name : $expiringTicket->worker->auth_token }}</b>                                

                                @if($expiringTicket->expired)
                                    is <span class="label label-danger customExpiredLabel">EXPIRED</span>
                                @elseif ($expiringTicket->expiresInDays < 1)
                                    expires <span class="label label-danger customDangerLabel"> TONIGHT </span>
                                @elseif ($expiringTicket->expiresInDays == 1 )
                                    expires <span class="label label-danger customDangerLabel"> TOMORROW NIGHT </span>
                                @elseif ($expiringTicket->expiresInDays < 14)
                                    expires in <span class="label label-danger customDangerLabel">{{ $expiringTicket->expiresInDays }} DAYS </span>
                                @elseif ($expiringTicket->expiresInDays >= 14 && $expiringTicket->expiresIn < 30)
                                    expires in <span class="label label-warning customWarningLabel">{{ $expiringTicket->expiresInDays }} DAYS </span>
                                @endif
                            </div>
                            <hr/>
                        @endforeach
                    @else
                        <div class='recentItem text-center'>
                            You have no ticket feed activity
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    @parent 
    <script src="{{{ asset('assets/js/moment-js/moment-with-locales.js') }}}"></script>
    <script src="{{{ asset('assets/js/jquery/jquery.simpleWeather.min.js') }}}"></script>
    <script src="{{{ asset('assets/js/wkss/dashboard.js') }}}"></script>
    <script src="{{{ asset('assets/js/chart.min.js') }}}"></script>
    <script>
        // MOVE THIS TO SEPARATE FILE 

        // Get context with jQuery - using jQuery's .get() method.
        var ctx = $("#workerEngagementChart").get(0).getContext("2d");

        var options = {
            responsive: true,
            // Make all info white colour
            scaleFontColor: "#FFFFFF",
            // Turn off vertical labels for a cleaner look
            scaleShowLabels : false, // to hide vertical lables
            ///Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines : false,
            //String - Colour of the grid lines
            scaleGridLineColor : "rgba(0,0,0,.05)",
            //Number - Width of the grid lines
            scaleGridLineWidth : 1,
            //Boolean - Whether to show horizontal lines (except X axis)
            scaleShowHorizontalLines: true,
            //Boolean - Whether to show vertical lines (except Y axis)
            scaleShowVerticalLines: true,
            //Boolean - Whether the line is curved between points
            bezierCurve : true,
            //Number - Tension of the bezier curve between points
            bezierCurveTension : 0.4,
            //Boolean - Whether to show a dot for each point
            pointDot : true,
            //Number - Radius of each point dot in pixels
            pointDotRadius : 4,
            //Number - Pixel width of point dot stroke
            pointDotStrokeWidth : 1,
            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
            pointHitDetectionRadius : 20,
            //Boolean - Whether to show a stroke for datasets
            datasetStroke : true,
            //Number - Pixel width of dataset stroke
            datasetStrokeWidth : 5,
            //Boolean - Whether to fill the dataset with a colour
            datasetFill : false,
            //String - A legend template
            legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].strokeColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"
        };

        <?php
            $months = array();
            $numberOfForms = array();
            foreach(Company::getUserEngagementLevelsForPreviousMonths(Auth::user(), 6) as $month){
                //echo $month['month'] . ' Amount:' . $month['numForms'] . '<br/>';
                array_push($months, $month['month']);
                array_push($numberOfForms, $month['numForms']);
            }
        ?>

        var data = {
            labels: <?php echo json_encode($months); ?>,/*["January", "February", "March", "April", "May", "June", "July"],*/
            datasets: [
                {
                    label: "My First dataset",
                    fillColor: "#fff",
                    strokeColor: "#fff",
                    pointColor: "#fff",
                    pointStrokeColor: "#fff",
                    pointHighlightFill: "#fff",
                    pointHighlightStroke: "#fff",
                    data: <?php echo json_encode($numberOfForms); ?>/*[65, 59, 80, 81, 56, 55, 40]*/
                }
            ]
        };

        // This will get the first returned node in the jQuery collection.
        var myLineChart = new Chart(ctx).Line(data, options);
    </script>
@stop
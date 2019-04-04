<!DOCTYPE HTML>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="White Knight Safety Solutions"/>

        <link rel="icon" type="image/x-icon" href="{{{ asset('favicon.ico') }}}" />

        <title>
            @section('title')
                White Knight Safety Solutions
            @show
        </title>

        @section('styles')
            <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
            <link href="{{{ asset('assets/css/jquery-ui/ui-lightness/jquery-ui-1.10.4.custom.css') }}}" rel="stylesheet"/>
            <link href="{{{ asset('assets/css/wkss/master.css') }}}" rel="stylesheet">
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/dt-1.10.9,r-1.0.7,sc-1.3.0,se-1.0.1/datatables.min.css"/>
            <link href="{{{ asset('assets/css/jquery-ui/jquery-confirm.min.css') }}}" rel="stylesheet">
        @show

        @yield('bodyStyle')
    </head>

    <body>
        @yield('body')
    </body>

    @section('scripts')
        <!-- JQuery -->
        <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
        <script src='https://code.jquery.com/ui/1.11.3/jquery-ui.min.js'></script>
        <script src="{{{ asset('assets/js/jquery/jquery-confirm.min.js') }}}"></script>

        <!-- Support Widget -->
        <script type="text/javascript" src="https://s3.amazonaws.com/assets.freshdesk.com/widget/freshwidget.js"></script>
        <script type="text/javascript">
            FreshWidget.init("", {"queryString": "&widgetType=popup", "widgetType": "popup", "buttonType": "text", "buttonText": "Support", "buttonColor": "white", "buttonBg": "#ff5608", "alignment": "4", "offset": "235px", "formHeight": "500px", "url": "https://whiteknightsafety.freshdesk.com"} );
        </script>

        <!--DataTables!-->
        <script type="text/javascript" src="https://cdn.datatables.net/r/dt/dt-1.10.9,r-1.0.7,sc-1.3.0,se-1.0.1/datatables.min.js"></script>
       <!-- <script src='https://cdn.datatables.net/1.10.8/js/jquery.dataTables.min.js'></script>-->
        <script src="{{{ asset('assets/js/datatables/datatable-initializations.js') }}}"></script>
        
        <!-- Bootstrap -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

        <script src="{{{ asset('assets/js/knockout-3.3.0.min.js') }}}"></script>
        <script>
            // Used extensively throughout various JavaScript files
            var site = "{{ URL::to('/') }}"+'/';
        </script>
    @show
</html>
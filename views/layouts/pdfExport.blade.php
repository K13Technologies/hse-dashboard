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
            <link href="{{{ asset('assets/css/wkss/master.css') }}}" rel="stylesheet">
            <link href="{{{ asset('assets/css/wkss/pdfExport.css') }}}" rel="stylesheet">
        @show
    </head>

    <body>
        @yield('content')

         <div id="footer">
            @section('footer')
            Copyright 2013 - {{ date('Y') }} White Knight Safety Solutions Inc. All Rights Reserved. <br/>
            <a href="https://whiteknightsafety.com"> www.whiteknightsafety.com  </a>
            @show
        </div>
    </body></html>
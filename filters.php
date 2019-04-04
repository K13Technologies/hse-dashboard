<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
//	if (!$request->secure()) {
//            return Redirect::secure($request->getRequestUri());
//	}
});


App::after(function($request, $response)
{       
	if (starts_with($request->path(), 'api')){
		$logger = new \Monolog\Logger('wkss');
		$logFile = storage_path().'/logs/wkss.txt';
		$logger->pushHandler(new \Monolog\Handler\RotatingFileHandler($logFile, 30));
		$logString = date('Y-m-d H:i:s').PHP_EOL.''.$request->getMethod().' || '. $request->getURI().' || '.$request->getContent().PHP_EOL.
		             $response->getStatusCode().' || '.$response->getContent().PHP_EOL.PHP_EOL;
		$logger->info($logString);
	}
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::guest('login');
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});
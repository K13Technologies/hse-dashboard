<?php

Route::filter('authenticatedFilter', function()
{   
    if (Auth::guest()) {
        return Redirect::guest('login');
    } 
});

Route::filter('subscription',function(){
    if(!Auth::user()->isAdmin()){
        $company = Auth::user()->company;

        if ($company->is_enterprise){
            if (!$company->onGracePeriod()) {
                return Redirect::to('login')->with(array('message'=>'Your subscription expired. Please contact the WKS sales department.'));
            }
        } else{
            $daysLeft = $company->trialDaysLeft();
            $daysLeftGrace = $company->gracePeriodDaysLeft();
            // If the company never subscribed and no trial left
            // OR The company did subscribe and then cancelled and they are not on their grace period
            if ((!$company->everSubscribed() && !$company->onTrial()) || ($company->everSubscribed() && $company->cancelled() && !$company->onGracePeriod())) {
                return Redirect::to('billing/details');
            }
        }
    }
});


// If the user is still on an old plan, we need to redirect them to a page where they can upgrade to the new plan. 
Route::filter('notOnOldPlan',function(){
    if(!Auth::user()->isAdmin()){
        $company = Auth::user()->company;

        if (!$company->is_enterprise){
            // If they have subscribed in the past and their plan is the old one
            if ($company->stripe_plan != NULL && $company->stripe_plan == BillingController::TIER_1_PRICING) {
                // Force company to update card and move to new subscription 
                return Redirect::to('billing/upgrade-to-annual');
            }
        }
    }
});

Route::filter('notEnterprise',function(){
    if(!Auth::user()->isAdmin()){
        $company = Auth::user()->company;
        if ($company->is_enterprise){
            // Enterprise companies should be redirected
            return Redirect::to('/');
        }
    }
});

Route::filter('hasRights', function($requestRoute) {   
    $user = Auth::user();
    $role = $user->role_id;
    if (!Session::has('tz_offset'))
    {
        Session::forget('tz_offset');
    }
    Session::put('tz_offset',$user->tz_offset);
    $userLevel = Config::get("webApp::userRoles.{$role}");
    // dd($userLevel);
    $routes = $userLevel['routes'];
    $requestPath = trim($requestRoute->getPath(),'/');
    $requestMethod = strtolower(head($requestRoute->getMethods()));

    if ($requestPath){
        if (!array_key_exists($requestPath, $routes)){
                return Response::make('Forbidden', 403);
        }else{
            $methods = explode('|',$routes[$requestPath]);
            if (!in_array($requestMethod, $methods)){
                return Response::make('Method not allowed', 405);
            }
        }
    }
});

Route::filter('resetTokenExists', function($route)
{   
    $resetToken = $route->getParameter('resetToken');
    $admin = Admin::getByResetToken($resetToken);
    if ( !$admin instanceof Admin){
        App::abort(404);
    }
});
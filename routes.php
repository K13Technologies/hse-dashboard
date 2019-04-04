<?php

// Used as a quick fix in checking for server requests for assets over the web -- solves an issue where PDFs could not access resources
// A permanent solution will be required in the future because IPs can be spoofed
global $serverIps;
$GLOBALS['serverIps'] = array('158.85.110.166',
                              '158.85.110.165',
                              '158.85.110.164'
                            );
$GLOBALS['userIP'] = Request::getClientIp();
// END QUICKFIX

Route::get('cloak/{userid}',function($userId){
    Auth::loginUsingId($userId);
    return Redirect::to('admin-management');
});

Route::get('login', function(){
    return View::make('webApp::auth.login');
});

Route::post('login', array('uses'=>'AuthController@loginAction'));

Route::get('logout', function(){
    Auth::logout();
    return Redirect::to('login');
});

Route::get('download-app', function(){
    return View::make('webApp::indexes.download-app');
});

Route::post('download-app', array('uses'=>'MiscController@downloadAppAction'));

Route::get('image/no-photo',function(){
        return AjaxController::createImageResponse(StorageManager::noPhoto());
});

Route::get('image/{photoId}',function($photoId){
    $photo = Photo::getPhotoByPhotoName($photoId);
    if ($photo instanceOf Photo){
        $content = $photo->path;
        return AjaxController::createImageResponse($content);
    }else{
        App::abort(404);
    }
});

Route::get('image/worker/signature/{authToken}',function($authToken){
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();

    /* // USED FOR TESTING PURPOSES
    $data = array('email'=>'email@email.com',
                  'link'=>$requestor);*/

    /*Mail::send('webApp::emails.password-recovery', $data, function($message) use ($data){
        $message->to($data['email'])->subject('test data');
    });*/

    $worker = Worker::getByAuthToken($authToken);
    if ($worker instanceOf Worker){
        // Check if it is the server making the request first -- temporary fix
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps'])|| $loggedUser->company_id == $worker->company_id || $loggedUser->isAdmin()) {
            $signatureId = $worker->signature();
            $storageManager = new StorageManager();
            return AjaxController::createImageResponse($storageManager->getSignaturePhoto($worker,$signatureId));
        } else{
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/review/signature/{reviewId}',function($reviewId){
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $review = SafetyFormReview::find($reviewId);
    $adminWhoReviewedForm = Admin::find($review->added_by);

    if ($review instanceOf SafetyFormReview && $adminWhoReviewedForm instanceOf Admin){
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $adminWhoReviewedForm->company_id || $loggedUser->isAdmin()) {
            $signaturePath = $review->signature();
            if($signaturePath){
                return AjaxController::createImageResponse($signaturePath);
            } else {
                App::abort(404);
            }
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
    }else{
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/admin/profile/signature',function(){
    $admin = Auth::user();
    if ($admin instanceOf Admin){
        $signaturePath = $admin->signature(); 
        if($signaturePath){
            return AjaxController::createImageResponse($signaturePath);
        }
        App::abort(404);
    }else{
        App::abort(404);
    }
});

Route::get('image/worker/profile/{authToken}',function($authToken){
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $worker = Worker::getByAuthToken($authToken);

    if (!$worker instanceof Worker){
        $worker = Worker::getByApiKey($authToken);
    }

    if ($worker instanceOf Worker){
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps'])  || $loggedUser->company_id == $worker->company_id || $loggedUser->isAdmin()) {
            $storageManager = new StorageManager();
            $photoPath = $storageManager->getProfilePhoto($worker);
            if(is_file($photoPath)){
               return AjaxController::createImageResponse($photoPath);
            }else{
               return AjaxController::createImageResponse(StorageManager::noPhoto());
            }
        }
        else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
    }else{
        //App::abort(404);
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

// This is a duplicate of the above route, but is for worker thumbnails. Dont want to modify the original in order to reduce risk of breaking something.
Route::get('image/worker/profile_thumb/{authToken}',function($authToken){
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $worker = Worker::getByAuthToken($authToken);

    if (!$worker instanceof Worker){
        $worker = Worker::getByApiKey($authToken);
    }

    if ($worker instanceOf Worker){
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps'])  || $loggedUser->company_id == $worker->company_id || $loggedUser->isAdmin()) {
            $storageManager = new StorageManager();
            $photoPath = $storageManager->getThumbProfilePhoto($worker);
            if(is_file($photoPath)){
               return AjaxController::createImageResponse($photoPath);
            }else{
               return AjaxController::createImageResponse(StorageManager::noPhoto());
            }
        }
        else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
    }else{
        //App::abort(404);
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});


// This doesn't appear to be used for anything currently because signoff workers can't have photos yet; only signatures 
Route::get('image/flha/visitor/{visitorId}/profile',function($visitorId){
    // WE MUST CHECK IF THEY ARE ALLOWED TO VIEW THIS
    $visitor = SignoffVisitor::find($visitorId);
    if ($visitor instanceOf SignoffVisitor){
        $storageManager = new StorageManager();
        return AjaxController::createImageResponse($storageManager->getSignoffVisitorPhoto($visitor));
    }else{
        App::abort(404);
    }
});

Route::get('image/flha/visitor/{visitorId}/signature',function($visitorId){
    // All of these security checks are not necessarily ideal, but they are necessary.
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $visitor = SignoffVisitor::find($visitorId);
    if($visitor) {
        $flha = Flha::find($visitor->flha_id);
        $workerWhoAddedFlha = Worker::find($flha->worker_id);
        
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps'])  || $loggedUser->company_id == $workerWhoAddedFlha->company_id || $loggedUser->isAdmin()) {
            if ($visitor instanceOf SignoffVisitor){
                $storageManager = new StorageManager();
                return AjaxController::createImageResponse($storageManager->getSignoffVisitorSignature($visitor));
            }else{
                App::abort(404);
            }
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

// This doesn't appear to be used for anything currently because signoff workers can't have photos yet; only signatures 
Route::get('image/flha/worker/{workerId}/profile',function($workerId){
    // WE MUST CHECK IF THEY ARE ALLOWED TO VIEW THIS
    // This permission checking should actually probably be in the filters file
    $worker = SignoffWorker::find($workerId);
    if ($worker instanceOf SignoffWorker){
        $storageManager = new StorageManager();
        return AjaxController::createImageResponse($storageManager->getSignoffWorkerPhoto($worker));
    }else{
        App::abort(404);
    }
});

Route::get('image/flha/worker/{workerId}/signature',function($workerId){
    // All of these security checks are not necessarily ideal, but they are necessary.
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $worker = SignoffWorker::find($workerId);
    if($worker) {
        $flha = Flha::find($worker->flha_id);
        $workerWhoAddedFlha = Worker::find($flha->worker_id);
        
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $workerWhoAddedFlha->company_id || $loggedUser->isAdmin()) {
            $worker = SignoffWorker::find($workerId);
            if ($worker instanceOf SignoffWorker){
                $storageManager = new StorageManager();
                return AjaxController::createImageResponse($storageManager->getSignoffWorkerSignature($worker));
            }else{
                App::abort(404);
            }
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/flha/spotcheck/{spotcheckId}/signature',function($spotcheckId){
    // All of these security checks are not necessarily ideal, but they are necessary.
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $spotcheck = Spotcheck::find($spotcheckId);
    if($spotcheck) {
        $flha = Flha::find($spotcheck->flha_id);
        $workerWhoAddedFlha = Worker::find($flha->worker_id);

        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $workerWhoAddedFlha->company_id || $loggedUser->isAdmin()) {
            if ($spotcheck instanceOf Spotcheck){
                $storageManager = new StorageManager();
                return AjaxController::createImageResponse($storageManager->getSpotcheckSignaturePhoto($spotcheck));
            }else{
                App::abort(404);
            }
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/tailgates/visitor/{visitorId}/signature',function($workerId){
    // All of these security checks are not necessarily ideal, but they are necessary.
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $visitor = TailgateSignoffVisitor::find($workerId);
    if($visitor instanceOf TailgateSignoffVisitor) {
        $tailgate = Tailgate::find($visitor->tailgate_id);
        $workerWhoAddedTailgate = Worker::find($tailgate->worker_id);
        
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $workerWhoAddedTailgate->company_id || $loggedUser->isAdmin()) {
            $storageManager = new StorageManager();
            return AjaxController::createImageResponse($storageManager->getTailgateSignoffVisitorSignature($visitor)); 
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/tailgates/worker/{workerId}/signature',function($workerId){
    // All of these security checks are not necessarily ideal, but they are necessary.
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $worker = TailgateSignoffWorker::find($workerId);
    if($worker instanceOf TailgateSignoffWorker) {
        $tailgate = Tailgate::find($worker->tailgate_id);
        $workerWhoAddedTailgate = Worker::find($tailgate->worker_id);
        
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $workerWhoAddedTailgate->company_id || $loggedUser->isAdmin()) {
            $storageManager = new StorageManager();
            return AjaxController::createImageResponse($storageManager->getTailgateSignoffWorkerSignature($worker)); 
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    } else {
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});

Route::get('image/daily-signin/{dailySigninId}/signature',function($dailySigninId){
    // This permission checking should actually probably be in the filters file
    $loggedUser = Auth::user();
    $dailySignin = DailySignin::find($dailySigninId);

    if ($dailySignin instanceOf DailySignin){
        if(in_array($GLOBALS['userIP'], $GLOBALS['serverIps']) || $loggedUser->company_id == $dailySignin->company_id || $loggedUser->isAdmin()) {
            $storageManager = new StorageManager();
            $photoPath = $storageManager->getDailySigninSignature($dailySignin);
            if(is_file($photoPath)){
               return AjaxController::createImageResponse($photoPath);
            }else{
               return AjaxController::createImageResponse(StorageManager::noPhoto());
            }
        } else {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
    }else{
        die('You are not authorized to perform this function. Your IP address has been logged.');
    }
});



Route::get('forgot-password', function(){
     return View::make('webApp::auth.forgot-password');
});

Route::post('forgot-password', array('uses'=>'AuthController@forgotPasswordAction'));


Route::group(array('before' => 'resetTokenExists'), function()
{
    Route::get('reset-password/{resetToken}', function($resetToken){
         return View::make('webApp::auth.reset-password')->with('resetToken',$resetToken);
    });

    Route::post('reset-password/{resetToken}', array('uses'=>'AuthController@resetPasswordAction'));
});

Route::group(array('before' => 'authenticatedFilter|hasRights'), function()
{
    Route::get('company-logo',function(){
        $companyLogo = Auth::user()->company->logo()->path;
        return AjaxController::createImageResponse($companyLogo);
    });
    
    
        Route::post('cards/review', function(){
        $input = Input::all();
        $class = $input['resource_type'];
        $resourceId = $input['resource_id'];
        $res = $class::find($resourceId);
        if (!$res->review instanceof SafetyFormReview){
            $review = new SafetyFormReview;
            $reviewerName = trim(Auth::user()->first_name.' '.Auth::user()->last_name);
            $review->reviewer_name = $reviewerName?$reviewerName:Auth::user()->email;
            $review->added_by = Auth::user()->admin_id;
            $review->ts = time();
            $review->created_at = date('Y-m-d H-i-s ZZZ');
            $review->reviewable_type = $class;
            $review->reviewable_id = $resourceId;
            $review->save();
            return AjaxController::buildAjaxResponse(200);
        }
        return AjaxController::buildAjaxResponse(200, array('error'=>'Already reviewed'));
        
    });
    
    
    
    Route::get('profile/disable-tutorial',function(){
        if (Auth::check()){
            Auth::user()->show_tutorial = false;
            Auth::user()->save();
        }
    });

    Route::post('profile',array('uses'=>'UserManagementController@updateProfileAction'));
    
    
});

Route::group(array('before' => 'authenticatedFilter|hasRights|subscription|notOnOldPlan'), function()
{
    Route::get('/', array('uses'=>'MiscController@indexView'));
    Route::get('admin-management', array('uses'=>'UserManagementController@manageUsersView'));
    Route::post('admin-management/add-admin', array('uses'=>'UserManagementController@addUserAction'));
    Route::post('admin-management/edit-admin', array('uses'=>'UserManagementController@editUserAction'));
    Route::post('admin-management/delete-admin', array('uses'=>'UserManagementController@deleteUserAction'));
    Route::get('company-management', array('uses'=>'CompanyManagementController@manageCompaniesView'));
    
    Route::pattern('refDate', '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])');
    
    Route::get('company-management/stats/{groupId?}/{refDate?}/{timeframe?}', array('uses'=>'CompanyManagementController@viewStatsAction'));
    Route::get('company-management/stats/export/{groupId}/{refDate}/{timeframe}', array('uses'=>'CompanyManagementController@exportStatsAction'));
    Route::post('company-management/stats/mail/{groupId}/{refDate}/{timeframe}', array('uses'=>'CompanyManagementController@mailStatsAction'));
    
    Route::get('company-management/{companyId}', array('uses'=>'CompanyManagementController@manageOneCompanyView'))
            ->where(array('id' => '[0-9]+'));
    Route::post('company-management/add-enterprise-company', array('uses'=>'CompanyManagementController@addEnterpriseCompanyAction'));
    Route::post('company-management/edit-company', array('uses'=>'CompanyManagementController@editCompanyAction'));
    Route::post('company-management/delete-company', array('uses'=>'CompanyManagementController@deleteCompanyAction'));    
    Route::post('company-management/company-projects', array('uses'=>'CompanyManagementController@getCompanyProjectsAction'));
    Route::post('company-management/company-divisions', array('uses'=>'CompanyManagementController@getCompanyDivisionsAction'));
    Route::post('company-management/division-business-units', array('uses'=>'CompanyManagementController@getDivisionBusinessUnitsAction'));
    Route::post('company-management/business-unit-groups', array('uses'=>'CompanyManagementController@getBusinessUnitGroupsAction'));
    Route::post('company-management/add-division', array('uses'=>'CompanyManagementController@addDivisionAction'));
    Route::post('company-management/edit-division', array('uses'=>'CompanyManagementController@editDivisionAction'));
    Route::post('company-management/delete-division', array('uses'=>'CompanyManagementController@deleteDivisionAction'));
    Route::post('company-management/add-business-unit', array('uses'=>'CompanyManagementController@addBusinessUnitAction'));
    Route::post('company-management/edit-business-unit', array('uses'=>'CompanyManagementController@editBusinessUnitAction'));
    Route::post('company-management/delete-business-unit', array('uses'=>'CompanyManagementController@deleteBusinessUnitAction'));
    Route::post('company-management/add-group', array('uses'=>'CompanyManagementController@addGroupAction'));
    Route::post('company-management/edit-group', array('uses'=>'CompanyManagementController@editGroupAction'));
    Route::post('company-management/delete-group', array('uses'=>'CompanyManagementController@deleteGroupAction'));
    Route::post('company-management/add-helpline', array('uses'=>'CompanyManagementController@addHelplineAction'));
    Route::post('company-management/delete-helpline', array('uses'=>'CompanyManagementController@deleteHelplineAction'));
    
    Route::get('upload-company-logo', array('uses'=>'CompanyManagementController@logoView'));
    Route::post('upload-company-logo', array('uses'=>'CompanyManagementController@uploadLogoAction'));
  
    Route::get('worker-management', array('uses'=>'WorkerManagementController@manageWorkersView'));
    Route::post('worker-management/add-worker', array('uses'=>'WorkerManagementController@addWorkerAction'));
    Route::get('worker-management/get-worker-details/{authToken}', array('uses'=>'WorkerManagementController@getWorkerDetailsAction'));
    Route::post('worker-management/edit-worker', array('uses'=>'WorkerManagementController@editWorkerAction'));
    Route::post('worker-management/disable-worker', array('uses'=>'WorkerManagementController@disableWorkerAction'));
    Route::post('worker-management/delete-worker', array('uses'=>'WorkerManagementController@deleteWorkerAction'));
    Route::post('worker-management/enable-worker', array('uses'=>'WorkerManagementController@enableWorkerAction'));
    
    Route::get('vehicle-management', array('uses'=>'VehicleManagementController@manageVehiclesView'));
    Route::post('vehicle-management/add-vehicle', array('uses'=>'VehicleManagementController@addVehicleAction'));
    Route::get('vehicle-management/get-vehicle-details/{vehicleId}', array('uses'=>'VehicleManagementController@getVehicleDetailsAction'));
    Route::post('vehicle-management/edit-vehicle', array('uses'=>'VehicleManagementController@editVehicleAction'));
    Route::post('vehicle-management/delete-vehicle', array('uses'=>'VehicleManagementController@deleteVehicleAction'));
    Route::get('vehicle-management/view/{vehicleId}/{inspectionId?}', array('uses'=>'VehicleManagementController@vehicleDetailsView'));
    Route::get('vehicle-management/export/{vehicleId}/{inspectionId?}', array('uses'=>'VehicleManagementController@vehicleDetailsExport'));
    Route::post('vehicle-management/add-action', array('uses'=>'VehicleManagementController@addInspectionAction'));
    Route::post('vehicle-management/mail', array('uses'=>'VehicleManagementController@vehicleDetailsMail'));  
    Route::get('vehicle-management/get-inspection-details/{inspectionId}', array('uses'=>'VehicleManagementController@getVehicleInspectionDetailsAction'));
    Route::post('vehicle-management/delete-vehicle', array('uses'=>'VehicleManagementController@deleteVehicleAction'));  
    
    Route::get('near-misses', array('uses'=>'NearMissWebController@nearMissesView'));
    Route::get('near-misses/view/{nearMissId}', array('uses'=>'NearMissWebController@nearMissDetailsView'));
    Route::get('near-misses/export/{nearMissId}', array('uses'=>'NearMissWebController@nearMissDetailsExport'));
    Route::post('near-misses/mail', array('uses'=>'NearMissWebController@nearMissDetailsMail'));
    Route::post('near-misses/add-action', array('uses'=>'NearMissWebController@addAction'));
    Route::post('near-misses/delete-near-miss', array('uses'=>'NearMissWebController@deleteNearMissAction'));
    Route::post('near-misses/edit-near-miss', array('uses'=>'NearMissWebController@editNearMissAction'));
    
    Route::get('hazard-cards', array('uses'=>'HazardWebController@hazardCardsView'));
    Route::get('hazard-cards/view/{hazardId}', array('uses'=>'HazardWebController@hazardCardDetailsView'));
    Route::get('hazard-cards/export/{hazardId}', array('uses'=>'HazardWebController@hazardCardDetailsExport'));
    Route::post('hazard-cards/mail', array('uses'=>'HazardWebController@hazardCardDetailsMail'));
    Route::post('hazard-cards/add-action', array('uses'=>'HazardWebController@addAction'));
    Route::post('hazard-cards/delete-hazard-card', array('uses'=>'HazardWebController@deleteHazardAction'));
    Route::post('hazard-cards/edit-hazard-card', array('uses'=>'HazardWebController@editHazardAction'));
    
    Route::get('field-observations', array('uses'=>'PositiveObservationWebController@positiveObservationsView'));
    Route::get('field-observations/view/{positiveObservationId}', array('uses'=>'PositiveObservationWebController@positiveObservationDetailsView'));
    Route::get('field-observations/export/{positiveObservationId}', array('uses'=>'PositiveObservationWebController@positiveObservationDetailsExport'));
    Route::post('field-observations/mail', array('uses'=>'PositiveObservationWebController@positiveObservationDetailsMail'));
    Route::post('field-observations/add-action', array('uses'=>'PositiveObservationWebController@addAction'));
    Route::post('field-observations/delete-observation', array('uses'=>'PositiveObservationWebController@deleteObservationAction'));
    Route::post('field-observations/edit-observation', array('uses'=>'PositiveObservationWebController@editObservationAction'));

    Route::get('journeys', array('uses'=>'JourneyWebController@journeysView'));
    Route::get('journey-management/view/{journeyId}', array('uses'=>'JourneyWebController@journeyDetailsView'));
    Route::post('journey-management/delete-journey', array('uses'=>'JourneyWebController@deleteJourneyAction'));

    Route::get('flha', array('uses'=>'FlhaWebController@flhasView'));
    Route::get('flha/view/{flhaId}', array('uses'=>'FlhaWebController@flhaDetailsAction'));
    Route::get('flha/export/{flhaId}', array('uses'=>'FlhaWebController@flhaDetailsExport'));
    Route::post('flha/mail', array('uses'=>'FlhaWebController@flhaDetailsExportMail'));
    Route::get('flha/get-visitor-details/{visitorId}', array('uses'=>'FlhaWebController@getVisitorDetailsAction'));
    Route::get('flha/get-worker-details/{workerId}', array('uses'=>'FlhaWebController@getWorkerDetailsAction'));
    Route::get('flha/get-spotcheck-details/{spotcheckId}', array('uses'=>'FlhaWebController@getspotcheckDetailsAction'));
    Route::post('flha/delete-flha', array('uses'=>'FlhaWebController@flhaDeleteAction'));
    Route::post('flha/edit-flha', array('uses'=>'FlhaWebController@editFlhaAction'));

    Route::get('faq', array('uses'=>'MiscController@faqPageView'));
    Route::get('faq/manage', array('uses'=>'MiscController@faqAddPageView'));
    Route::post('faq/add', array('uses'=>'MiscController@faqAddPageAction'));
    Route::post('faq/edit', array('uses'=>'MiscController@faqEditPageAction'));
    Route::post('faq/delete', array('uses'=>'MiscController@faqDeletePageAction'));

    Route::pattern('signDate', '[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])');
    Route::get('daily-signin/{groupId?}/{signDate?}', array('uses'=>'DailySigninWebController@dailySigninAction'));
    Route::get('daily-signin/export/{groupId}/{signDate}', array('uses'=>'DailySigninWebController@dailySigninExportAction'));
    Route::post('daily-signin/mail/{groupId}/{signDate}', array('uses'=>'DailySigninWebController@dailySigninMailAction'));
    
    Route::get('tailgates', array('uses'=>'TailgateWebController@tailgatesView'));
    Route::get('tailgates/view/{tailgateId}', array('uses'=>'TailgateWebController@tailgateDetailsAction'));
    Route::get('tailgates/export/{tailgateId}', array('uses'=>'TailgateWebController@tailgateDetailsExport')); 
    Route::post('tailgates/mail', array('uses'=>'TailgateWebController@tailgateDetailsExportMail'));
    Route::get('tailgates/get-visitor-details/{visitorId}', array('uses'=>'TailgateWebController@getVisitorDetailsAction'));
    Route::get('tailgates/get-worker-details/{workerId}', array('uses'=>'TailgateWebController@getWorkerDetailsAction'));
    Route::post('tailgates/delete-tailgate', array('uses'=>'TailgateWebController@tailgateDeleteAction'));
    Route::post('tailgates/edit-tailgate', array('uses'=>'TailgateWebController@editTailgateAction'));
    
    Route::get('incident-cards', array('uses'=>'IncidentWebController@incidentCardsView'));
    Route::get('incident-cards/view/{incidentId}', array('uses'=>'IncidentWebController@IncidentCardDetailsView'));
    Route::get('incident-cards/export/{incidentId}', array('uses'=>'IncidentWebController@IncidentCardDetailsExport'));
    Route::get('incident-cards/get-person-details/{personId}', array('uses'=>'IncidentWebController@getPersonDetailsAction'));
    Route::get('incident-cards/mvd/{incidentMVDId}', array('uses'=>'IncidentWebController@getIncidentMVDAction'));
    Route::post('incident-cards/mail', array('uses'=>'IncidentWebController@incidentCardDetailsMail'));
    Route::post('incident-cards/add-action', array('uses'=>'IncidentWebController@addAction'));
    Route::post('incident-cards/delete-incident', array('uses'=>'IncidentWebController@incidentDeleteAction'));
    Route::post('incident-cards/edit-incident', array('uses'=>'IncidentWebController@editIncidentAction'));

    Route::get('tickets', array('uses'=>'TicketWebController@ticketsView'));
    Route::get('tickets/create-ticket-view', array('uses' => 'TicketWebController@createTicketView'));
    Route::get('tickets/view/{ticketId}', array('uses' => 'TicketWebController@editTicketView'));
    Route::post('tickets/create', array('uses' => 'TicketWebController@createTicket'));
    Route::post('tickets/mail', array('uses' => 'TicketWebController@mailTicket'));
    Route::post('tickets/delete', array('uses' => 'TicketWebController@deleteTicket'));
    Route::post('tickets/update', array('uses' => 'TicketWebController@update'));
    Route::get('tickets/export/{ticketId}', array('uses'=>'TicketWebController@exportTicket'));

    Route::get('safety-manual', array('uses'=>'SafetyManualWebController@safetyManualView'));
    Route::post('safety-manual/edit-safety-manual', array('uses'=>'SafetyManualWebController@editSafetyManualAction'));
    Route::post('safety-manual/mail', array('uses'=>'SafetyManualWebController@mailSafetyManual'));
    Route::get('safety-manual/revisions', array('uses'=>'SafetyManualWebController@safetyManualRevisionView'));
    Route::get('safety-manual/section/{sectionid}', array('uses'=>'SafetyManualWebController@safetyManualSectionView'));
    Route::get('safety-manual/export/{sectionId?}/{subsectionId?}', array('uses'=>'SafetyManualWebController@ExportSafetyManual'));
    Route::post('safety-manual/mail/{sectionId?}/{subsectionId?}', array('uses'=>'SafetyManualWebController@MailSafetyManual'));  
});


// Should filter this, but then it won't seem to allow the action
// The method inside of the controller will do some authentication, anyway, during user and object lookup.
//Route::post('safety-manual/upload-image', array('uses'=>'SafetyManualWebController@uploadImageAction'));
Route::get('safety-manual/upload-image', function() {
    return View::make('webApp::safety-manual._image-dialog');
});

Route::post('safety-manual/upload-image', 'SafetyManualWebController@uploadImageAction');

Route::get('free-trial', function(){
    return View::make('webApp::billing.trial');
});

Route::post('free-trial', array('uses'=>'BillingController@freeTrialAction'));

// Only non enterprise companies should be able to see these pages 
Route::group(array('before' => 'notEnterprise'), function()
{
    Route::get('billing/credit-card-details', array('uses'=>'BillingController@creditCardDetailsView'));
    Route::post('billing/credit-card-details', array('uses'=>'BillingController@creditCardDetailsAction'));
    Route::get('billing/details', array('uses'=>'BillingController@detailsView'));
    Route::get('billing/upgrade-to-annual', array('uses'=>'BillingController@upgradeToAnnualView'));
    Route::post('billing/cancel-subscription/{companyId}', array('uses'=>'BillingController@cancelSubscriptionAction'));
    Route::post('billing/resume-subscription/{companyId}', array('uses'=>'BillingController@resumeSubscriptionAction'));
    Route::get('billing/invoice/export/{invoice_id}',array('uses'=>'BillingController@downloadInvoiceAction'));

    Route::post('billing/apply-subscription-coupon', array('uses'=>'BillingController@applySubscriptionCoupon'));
    Route::post('billing/remove-subscription-coupon', array('uses'=>'BillingController@removeSubscriptionCoupon'));
    // Use GET to check validity of coupon because the request is both safe and idempotent.
    Route::get('billing/check-coupon/{couponId}', array('uses'=>'BillingController@checkCouponValidity'));

    Route::get('purchase-services', array('uses'=>'BillingController@purchaseServicesView'));
    Route::post('purchase-services', array('uses'=>'BillingController@purchaseServiceAction'));
});


/*  From the Stripe docs: 
    If you're using Rails, Django, or another web framework, your site may automatically check that every POST request contains a CSRF token. 
    This is an important security feature that helps protect you and your users from cross-site request forgery attempts. 
    However, this security measure may also prevent your site from processing legitimate webhooks. If so, you may need to exempt the webhooks route from CSRF protection.
*/
Route::post('stripe/webhook', 'WebhookController@handleWebhook');
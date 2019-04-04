<?php

//Bootstrap
Route::get('api/bootstrap', 'ApiController@bootstrapAction');

Route::get('api/app-version', 'ApiController@appVersionAction');

Route::get('api/image/worker/profile/{apiKey}',function($apiKey){
    $worker = Worker::getByApiKey($apiKey);
    if ($worker instanceOf Worker){
        $storageManager = new StorageManager();
        $photoPath = $storageManager->getThumbProfilePhoto($worker);
        if(is_file($photoPath)){
           return AjaxController::createImageResponse($photoPath);
        }else{
           return AjaxController::createImageResponse(StorageManager::noPhoto());
        }
    }else{
        App::abort(404);
    }
});

Route::get('api/image/incident-schema/{schemaId}/{size}',function($schemaId,$size){
    $schema = IncidentSchema::find($schemaId);
    if ($schema instanceOf IncidentSchema){
        $storageManager = new StorageManager();
        $photoPath = $storageManager->getIncidentSchema($schema,$size);
        if(is_file($photoPath)){
            return AjaxController::createImageResponse($photoPath);
        }else{
            return AjaxController::createImageResponse(StorageManager::noPhoto());
        }
    }else{
        App::abort(404);
    }
});

Route::group(array('before' => 'requestFilter'), function()
{
    //Identify a worker
    Route::get('api/worker/login', 'WorkerController@loginAction');
    
    
    /**
     * A route that handles the register action
     * The registerFilter fails if the worker has completed his profile info
     * This means he already registered
     */
    Route::post('api/worker/register', array('before' => 'registerFilter', 
            'uses' => 'WorkerController@registerAction')
    );
    
    //For the rest of the API calls, the worker must have his profile completed

    Route::group(array('before' => 'profileCompletedFilter'), function()
    {
        Route::get('api/project/workers','ApiController@getWorkersForProjectAction');
        
        
        //this is for the get profile action
        Route::get('api/worker/profile', 'WorkerController@getProfileAction');

        //this is for the edit profile action
        Route::put('api/worker/profile', 'WorkerController@editProfileAction');

        //this is for the edit settings action
        Route::put('api/worker/settings', 'WorkerController@editSettingsAction');
        
        //this is for the get profile photo action
        Route::get('api/worker/profile-photo', 'WorkerController@getProfilePhotoAction');
        
        //this is for the add profile photo action
        Route::post('api/worker/profile-photo',  array('uses'=>'WorkerController@saveProfilePhotoAction'));
        
        
        //this is for the add signature action
        Route::post('api/worker/signatures',  array('uses'=>'WorkerController@saveSignatureAction'));
        
        //this is for the get signature action
        Route::get('api/worker/signatures/{signatureId}', 'WorkerController@getSignatureAction');
      
        //this is for the delete signature action
        Route::delete('api/worker/signatures/{signatureId}', 'WorkerController@deleteSignatureAction');
        

        /*=========SAFETY MANUAL ============*/

        Route::group(array('before' => 'hasSafetyManual'), function()
        {
            Route::get('api/safety-manual-sections', 'SafetyManualController@getSectionList');
            Route::get('api/safety-manual-subsections-metadata', 'SafetyManualController@getAllSubsectionsMetadata');
            Route::get('api/safetyManualPhotos/', 'SafetyManualController@getSafetyManualPhotoIdList');

            Route::group(array('before' => 'safetyManualSectionAccess'), function()
            {
                Route::get('api/safety-manual-sections/{sectionId}', 'SafetyManualController@getSection');
            });

            Route::group(array('before' => 'safetyManualSubsectionAccess'), function()
            {
                Route::get('api/safety-manual-subsections/{subsectionId}', 'SafetyManualController@getSubsection');
            });

            Route::group(array('before' => 'safetyManualPhotoAccess'), function()
            {
                Route::get('api/safetyManualPhoto/{photoId}', 'SafetyManualController@getSafetyManualPhoto');
            });
        });
        /*===================================*/

        /*========= TRAINING MATRIX TICKETS ============*/
        Route::get('api/tickets', 'TicketController@getAllTicketsForWorker');

        Route::group(array('before' => 'ticketPhotoAccess'), function()
        {
            Route::get('api/tickets/photos/{photoId}', 'TicketController@getTicketPhoto');
        });

        Route::group(array('before' => 'ticketApprovalAccess'), function()
        {
            Route::get('api/tickets/review/signature/{adminId}', 'TicketController@getTicketApprovalSignature');
        });
        /*==============================================*/
        
        //this handles the vehicle creation action
        Route::post('api/vehicles', 'VehicleController@addVehicleAction');
       
        //this handles the vehicle creation action
        Route::get('api/vehicles', 'VehicleController@getVehicleListAction');
        
        
        Route::group(array('before' => 'vehicleAccess'), function()
        {
            //this handles the vehicle edit action
            Route::get('api/vehicles/{vehicleId}', 'VehicleController@getVehicleAction');
            
            //this handles the vehicle edit action
            Route::put('api/vehicles/{vehicleId}', 'VehicleController@editVehicleAction');
            
            //this handles the inspection add
            Route::post('api/vehicles/{vehicleId}/inspections', 'InspectionController@addInspectionAction');
            
            
            
            //this handles the vehicle photo save action
            Route::post('api/vehicles/{vehicleId}/photos','VehicleController@saveVehiclePhotoAction');
        });
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            //this handles the vehicle photo get action
            Route::get('api/vehicles/photos/{photoId}', 'VehicleController@getVehiclePhotoAction');
             //this handles the vehicle photo delete action
            Route::delete('api/vehicles/photos/{photoId}', 'VehicleController@deleteVehiclePhotoAction');
        });
        
        
        
        Route::group(array('before' => 'inspectionAccess'), function()
        {
            //this handles the inspection add
            Route::get('api/vehicles/inspections/{inspectionId}', 'InspectionController@getInspectionAction');

            //this handles the inspection add
            Route::put('api/vehicles/inspections/{inspectionId}', 'InspectionController@editInspectionAction');
        
            //this handles the inspection delete
            Route::delete('api/vehicles/inspections/{inspectionId}', 'InspectionController@deleteInspectionAction');
      
            
            //this handles the vehicle photo save action
            Route::post('api/vehicles/inspections/{inspectionId}/photos','InspectionController@saveInspectionPhotoAction');
        });
        
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            //this handles the vehicle photo get action
            Route::get('api/vehicles/inspections/photos/{photoId}', 'InspectionController@getInspectionPhotoAction');
             //this handles the vehicle photo delete action
            Route::delete('api/vehicles/inspections/photos/{photoId}', 'InspectionController@deleteInspectionPhotoAction');
        });
        
        
        //this handles the hazard creation action
        Route::post('api/hazards', 'HazardController@addHazardAction');
        
        //this handles the hazard list retrieval action
        Route::get('api/hazards', 'HazardController@getHazardListAction');
        
        
      
        
        Route::group(array('before' => 'hazardAccess'), function()
        {
            Route::put('api/hazards/{hazardId}', 'HazardController@editHazardAction');
        
            Route::delete('api/hazards/{hazardId}', 'HazardController@deleteHazardAction');

            Route::post('api/hazards/{hazardId}/photos', 'HazardController@saveHazardPhotoAction');
        });
        
        
        
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            Route::get('api/hazards/photos/{photoId}', 'HazardController@getHazardPhotoAction');
        
            Route::delete('api/hazards/photos/{photoId}', 'HazardController@deleteHazardPhotoAction');
        });
        
        
         //this handles the hazard creation action
        Route::post('api/near-misses', 'NearMissController@addNearMissAction');
          //this handles the hazard list retrieval action
        Route::get('api/near-misses', 'NearMissController@getNearMissListAction');
        
        
        Route::group(array('before' => 'nearMissAccess'), function()
        {
            Route::put('api/near-misses/{nearMissId}', 'NearMissController@editNearMissAction');
        
            Route::delete('api/near-misses/{nearMissId}', 'NearMissController@deleteNearMissAction');

            Route::post('api/near-misses/{nearMissId}/photos', 'NearMissController@saveNearMissPhotoAction');
        });
        
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            Route::get('api/near-misses/photos/{photoId}', 'NearMissController@getNearMissPhotoAction');
        
            Route::delete('api/near-misses/photos/{photoId}', 'NearMissController@deleteNearMissPhotoAction');
        });
        
        
                
        //this handles the hazard creation action
        Route::post('api/positive-observations', 'PositiveObservationController@addPositiveObservationAction');
        
        //this handles the hazard list retrieval action
        Route::get('api/positive-observations', 'PositiveObservationController@getPositiveObservationListAction');
        
        
      
        
        Route::group(array('before' => 'positiveObservationAccess'), function()
        {
            Route::put('api/positive-observations/{positiveObservationId}', 'PositiveObservationController@editPositiveObservationAction');
        
            Route::delete('api/positive-observations/{positiveObservationId}', 'PositiveObservationController@deletePositiveObservationAction');

            Route::post('api/positive-observations/{positiveObservationId}/photos', 'PositiveObservationController@savePositiveObservationPhotoAction');
        });
        
        
        
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            Route::get('api/positive-observations/photos/{photoId}', 'PositiveObservationController@getPositiveObservationPhotoAction');
        
            Route::delete('api/positive-observations/photos/{photoId}', 'PositiveObservationController@deletePositiveObservationPhotoAction');
        });
        
       
        
        
        
        //this handles the FLHA creation action
        Route::post('api/flha', 'FlhaController@addFlhaAction');
        
        Route::group(array('before' => 'flhaAccess'), function()
        {
            Route::get('api/flha/{flhaId}', 'FlhaController@getOneFlhaAction');

            Route::put('api/flha/{flhaId}', 'FlhaController@editFlhaAction');
            
            Route::put('api/flha/{flhaId}/checklist', 'FlhaController@setFlhaChecklistAction');
            
            Route::post('api/flha/{flhaId}/tasks', 'FlhaController@addFlhaTaskAction');
            
            Route::post('api/flha/{flhaId}/photos', 'FlhaController@saveFlhaPhotoAction');
            
            Route::post('api/flha/{flhaId}/spotchecks', 'FlhaController@addFlhaSpotcheckAction');
            
            Route::post('api/flha/{flhaId}/complete', 'FlhaController@setFlhaJobCompletionAction');
            
            Route::post('api/flha/{flhaId}/visitor-signoff', 'FlhaController@addVisitorForSignoffAction');
            
            Route::post('api/flha/{flhaId}/worker-signoff', 'FlhaController@addWorkerForSignoffAction');
        
        });
        
        
        Route::group(array('before' => 'flhaTaskAccess'), function()
        {
            Route::delete('api/flha/tasks/{flhaTaskId}', 'FlhaController@deleteFlhaTaskAction');
           
            Route::get('api/flha/tasks/{flhaTaskId}', 'FlhaController@getFlhaTaskAction');
            
            Route::post('api/flha/tasks/{flhaTaskId}/hazards', 'FlhaController@addFlhaTaskHazardAction');
            
        });
        
        
        Route::group(array('before' => 'flhaTaskHazardAccess'), function()
        {
            //edit the flha description
            Route::get ('api/flha/tasks/hazards/{flhaTaskHazardId}', 'FlhaController@getFlhaTaskHazardAction');
          
            Route::delete('api/flha/tasks/hazards/{flhaTaskHazardId}', 'FlhaController@deleteFlhaTaskHazardAction');
            
            Route::put('api/flha/tasks/hazards/{flhaTaskHazardId}', 'FlhaController@editFlhaTaskHazardAction');
            
        });
        
        
        Route::group(array('before' => 'spotcheckAccess'), function()
        {
            Route::get('api/flha/spotchecks/{flhaSpotcheckId}', 'FlhaController@getFlhaSpotcheckAction');
          
            Route::put('api/flha/spotchecks/{flhaSpotcheckId}', 'FlhaController@editFlhaSpotcheckAction');
            
            Route::post('api/flha/spotchecks/{flhaSpotcheckId}/signature', 'FlhaController@saveFlhaSpotcheckSignatureAction');
            
            Route::get('api/flha/spotchecks/{flhaSpotcheckId}/signature', 'FlhaController@getFlhaSpotcheckSignatureAction');
             
        });
        
        
        Route::group(array('before' => 'signoffVisitorAccess'), function()
        {
            Route::get('api/flha/visitor-signoff/{visitorId}', 'FlhaController@getVisitorForSignoffAction');
           
            Route::put('api/flha/visitor-signoff/{visitorId}', 'FlhaController@editVisitorForSignoffAction');
//          
            Route::delete('api/flha/visitor-signoff/{visitorId}', 'FlhaController@deleteVisitorForSignoffAction');  
//         
            
            Route::post('api/flha/visitor-signoff/{visitorId}/photo', 'FlhaController@saveVisitorSignoffPhotoAction');
//            
            Route::get('api/flha/visitor-signoff/{visitorId}/photo', 'FlhaController@getVisitorSignoffPhotoAction');
          
            Route::post('api/flha/visitor-signoff/{visitorId}/signature', 'FlhaController@saveVisitorSignoffSignatureAction');
//            
            Route::get('api/flha/visitor-signoff/{visitorId}/signature', 'FlhaController@getVisitorSignoffSignatureAction');
//             
        });
        
        
        Route::group(array('before' => 'signoffWorkerAccess'), function()
        {
            Route::get('api/flha/worker-signoff/{workerId}', 'FlhaController@getWorkerForSignoffAction');
           
            Route::put('api/flha/worker-signoff/{workerId}', 'FlhaController@editWorkerForSignoffAction');
//          
            Route::delete('api/flha/worker-signoff/{workerId}', 'FlhaController@deleteWorkerForSignoffAction');  
//         
            Route::post('api/flha/worker-signoff/{workerId}/breaks', 'FlhaController@addBreakForSignoffAction');
//        
            
            Route::post('api/flha/worker-signoff/{workerId}/photo', 'FlhaController@saveWorkerSignoffPhotoAction');
//            
            Route::get('api/flha/worker-signoff/{workerId}/photo', 'FlhaController@getWorkerSignoffPhotoAction');
          
            Route::post('api/flha/worker-signoff/{workerId}/signature', 'FlhaController@saveWorkerSignoffSignatureAction');
//            
            Route::get('api/flha/worker-signoff/{workerId}/signature', 'FlhaController@getWorkerSignoffSignatureAction');
//             
        });
        
        Route::delete('api/flha/worker-signoff/breaks/{breakId}', array('before' =>'breakAccess',
                    'uses'=>'FlhaController@deleteBreakForSignoffAction')); 
        
        
        Route::group(array('before' => 'photoAccess'), function()
        {
            Route::get('api/flha/photos/{photoId}', 'FlhaController@getFlhaPhotoAction');
        
            Route::delete('api/flha/photos/{photoId}', 'FlhaController@deleteFlhaPhotoAction');
        });
        
        Route::get('api/flha/checklist', function(){
            return HazardChecklistCategory::with('hazardChecklistItems')->get()->toArray();
        });
        
        
        Route::post('api/journey-management/addresses','JourneyController@addLocationAction');
        
        Route::group(array('before' => 'journeyAddressAccess'), function()
        {
            Route::delete('api/journey-management/addresses/{addressId}','JourneyController@deleteLocationAction');
            
            Route::put('api/journey-management/addresses/{addressId}','JourneyController@editLocationAction');
            
        });
        
        Route::get('api/journey-management/addresses','JourneyController@getLocationListAction');
        
        Route::post('api/journey-management/start','JourneyController@startJourneyAction');
        
        Route::post('api/journey-management/checkin','JourneyController@checkinForJourneyAction');
        
        Route::get('api/journey-management/help-lines','JourneyController@helpLinesAction');

        
        Route::post('api/journey-management/v2/start','JourneyController@startJourneyV2Action');
        
        Route::put('api/journey-management/v2/change-endpoints','JourneyController@changeJourneyEndpointsV2Action');
        
        Route::post('api/journey-management/v2/checkin','JourneyController@checkinForJourneyV2Action');
        
        
        Route::post('api/daily-signin','DailySigninController@signinAction');
        
        Route::post('api/daily-signin/{dailySingninId}/signature','DailySigninController@saveDailySigninSignatureAction');
       
        
      
        
        
          //this handles the tailgate creation action
        Route::post('api/tailgate', 'TailgateController@addTailgateAction');
        
        Route::group(array('before' => 'tailgateAccess'), function()
        {
            Route::get('api/tailgate/{tailgateId}', 'TailgateController@getOneTailgateAction');

            Route::put('api/tailgate/{tailgateId}', 'TailgateController@editTailgateAction');
            
            Route::put('api/tailgate/{tailgateId}/checklist', 'TailgateController@setTailgateChecklistAction');
           
            Route::put('api/tailgate/{tailgateId}/hazard-assessment', 'TailgateController@setTailgateHazardAssessmentAction');
            
            Route::post('api/tailgate/{tailgateId}/tasks', 'TailgateController@addTailgateTaskAction');
            
            Route::post('api/tailgate/{tailgateId}/complete', 'TailgateController@setTailgateJobCompletionAction');
            
            Route::post('api/tailgate/{tailgateId}/visitor-signoff', 'TailgateController@addVisitorForSignoffAction');
            
            Route::post('api/tailgate/{tailgateId}/worker-signoff', 'TailgateController@addWorkerForSignoffAction');
        
        });
        
        
        Route::group(array('before' => 'tailgateTaskAccess'), function()
        {
            Route::delete('api/tailgate/tasks/{tailgateTaskId}', 'TailgateController@deleteTailgateTaskAction');
           
            Route::get('api/tailgate/tasks/{tailgateTaskId}', 'TailgateController@getTailgateTaskAction');
            
            Route::post('api/tailgate/tasks/{tailgateTaskId}/hazards', 'TailgateController@addTailgateTaskHazardAction');
            
        });
        
        
        Route::group(array('before' => 'tailgateTaskHazardAccess'), function()
        {

            Route::get ('api/tailgate/tasks/hazards/{flhaTaskHazardId}', 'TailgateController@getTailgateTaskHazardAction');
          
            Route::delete('api/tailgate/tasks/hazards/{flhaTaskHazardId}', 'TailgateController@deleteTailgateTaskHazardAction');
            
            Route::put('api/tailgate/tasks/hazards/{flhaTaskHazardId}', 'TailgateController@editTailgateTaskHazardAction');
            
        });
        
        
        Route::group(array('before' => 'tailgateSignoffVisitorAccess'), function()
        {
            Route::get('api/tailgate/visitor-signoff/{visitorId}', 'TailgateController@getVisitorForSignoffAction');
           
            Route::put('api/tailgate/visitor-signoff/{visitorId}', 'TailgateController@editVisitorForSignoffAction');
//          
            Route::delete('api/tailgate/visitor-signoff/{visitorId}', 'TailgateController@deleteVisitorForSignoffAction');  
//         
            
            Route::post('api/tailgate/visitor-signoff/{visitorId}/signature', 'TailgateController@saveVisitorSignoffSignatureAction');
//            
            Route::get('api/tailgate/visitor-signoff/{visitorId}/signature', 'TailgateController@getVisitorSignoffSignatureAction');
//             
        });
        
        
        Route::group(array('before' => 'tailgateSignoffWorkerAccess'), function()
        {
            Route::get('api/tailgate/worker-signoff/{workerId}', 'TailgateController@getWorkerForSignoffAction');
           
            Route::put('api/tailgate/worker-signoff/{workerId}', 'TailgateController@editWorkerForSignoffAction');
//          
            Route::delete('api/tailgate/worker-signoff/{workerId}', 'TailgateController@deleteWorkerForSignoffAction');  
            
            Route::post('api/tailgate/worker-signoff/{workerId}/signature', 'TailgateController@saveWorkerSignoffSignatureAction');
//            
            Route::get('api/tailgate/worker-signoff/{workerId}/signature', 'TailgateController@getWorkerSignoffSignatureAction');
//             
        });
        
        
        Route::post('api/incident','IncidentController@addIncidentAction');
       
        Route::get('api/incident','IncidentController@getIncidentListAction');
        
        Route::group(array('before'=>'incidentAccess'),function(){
            
            Route::get('api/incident/{incidentId}','IncidentController@getIncidentAction');
            
            Route::put('api/incident/{incidentId}','IncidentController@editIncidentAction');
            
            Route::post('api/incident/{incidentId}/persons','IncidentController@addIncidentPersonAction');
            
            
            Route::post('api/incident/{incidentId}/mvd','IncidentController@addIncidentMVDAction');
            
            Route::post('api/incident/{incidentId}/treatment','IncidentController@addIncidentTreatmentAction');
            
            
            Route::post('api/incident/{incidentId}/release-and-spills','IncidentController@addIncidentReleaseSpillAction');
            
            Route::get('api/incident/{incidentId}/release-and-spills','IncidentController@getIncidentReleaseSpillAction');
            
            Route::put('api/incident/{incidentId}/release-and-spills','IncidentController@editIncidentReleaseSpillAction');
            
            Route::delete('api/incident/{incidentId}/release-and-spills','IncidentController@deleteIncidentReleaseSpillAction');
            

            Route::post('api/incident/{incidentId}/photos', 'IncidentController@saveIncidentPhotoAction');
        });
        
        Route::group(array('before'=>'incidentPersonAccess'),function(){
            
            Route::get('api/incident/persons/{incidentPersonId}','IncidentController@getIncidentPersonAction');
            
            Route::put('api/incident/persons/{incidentPersonId}','IncidentController@editIncidentPersonAction');
            
            Route::delete('api/incident/persons/{incidentPersonId}','IncidentController@deleteIncidentPersonAction');
            
        });
        
        Route::group(array('before'=>'incidentStatementAccess'),function(){
        
            Route::post('api/incident/statement/{incidentStatementId}/photos','IncidentController@saveIncidentPartStatementPhotoAction');
            
            Route::put('api/incident/statement/{incidentStatementId}','IncidentController@editIncidentPartStatementAction');
             
            Route::delete('api/incident/statement/{incidentStatementId}','IncidentController@deleteIncidentPartStatementAction');
             
        });
        
        Route::group(array('before'=>'incidentMVDAccess'),function(){
            
            Route::get('api/incident/mvd/{incidentMVDId}','IncidentController@getIncidentMVDAction');
            
            Route::put('api/incident/mvd/{incidentMVDId}','IncidentController@editIncidentMVDAction');
            
            Route::delete('api/incident/mvd/{incidentMVDId}','IncidentController@deleteIncidentMVDAction');
            
            Route::post('api/incident/mvd/{incidentMVDId}/statement','IncidentController@addIncidentMVDPartStatementAction');
            
            Route::post('api/incident/mvd/{incidentMVDId}/photos','IncidentController@addIncidentMVDPhotoAction');
        
        });
        
        Route::group(array('before'=>'incidentMTAccess'),function(){
            
            
            Route::get('api/incident/treatment/{incidentTreatmentId}','IncidentController@getIncidentTreatmentAction');
            
            Route::put('api/incident/treatment/{incidentTreatmentId}','IncidentController@editIncidentTreatmentAction');
            
            Route::delete('api/incident/treatment/{incidentTreatmentId}','IncidentController@deleteIncidentTreatmentAction');
            
            Route::post('api/incident/treatment/{incidentTreatmentId}/statement','IncidentController@addIncidentTreatmentPartStatementAction');
            
        });
        
        Route::group(array('before'=>'photoAccess'),function(){
            
            Route::get('api/incident/statement/photos/{photoId}','IncidentController@getIncidentPartStatementPhotoAction');

            Route::delete('api/incident/statement/photos/{photoId}','IncidentController@deleteIncidentPartStatementPhotoAction');
          
            Route::get('api/incident/mvd/photos/{photoId}','IncidentController@getIncidentMVDPhotoAction');

            Route::delete('api/incident/mvd/photos/{photoId}','IncidentController@deleteIncidentMVDPhotoAction');
            
            Route::get('api/incident/photos/{photoId}', 'IncidentController@getIncidentPhotoAction');
        
            Route::delete('api/incident/photos/{photoId}', 'IncidentController@deleteIncidentPhotoAction');
             
        });  
        
        
    });
    
    
});






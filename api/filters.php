<?php

Route::filter('requestFilter', function()
{   
    //checks if content is set and is valid json
    $content = Request::getContent();
    if (!$content xor !json_decode(Request::getContent())){
       return Response::make(array('error'=>Config::get('api::responseConstants.invalidJson')),400);
    }   
    
    //checks if auth token is set
    if (!Input::has('authToken')){
        return Response::make(array('error'=>Config::get('api::responseConstants.authTokenMissing')),401);
    }
    
    //check if auth token exists
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    if (!$worker instanceof Worker || $worker->isDisabled() || $worker->deleted_at !=NULL ){
        return Response::make(array('error'=>Config::get('api::responseConstants.inexistingAuthToken')),404);
    }
});

/** Check so that a worker completed his profile
*   Fail if he didn't
*/
Route::filter('profileCompletedFilter', function()
{   
    $worker = Worker::getByAuthToken(Input::get('authToken')); 
    if (!$worker->profile_completed){
         return Response::make(array('error'=>Config::get('api::responseConstants.profileIncomplete')),401);
    }
});
/** Check so that a worker completed his profile
*   Fail if he did
*/
Route::filter('registerFilter', function()
{   
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    if ($worker->profile_completed){
         return Response::make(array('error'=>Config::get('api::responseConstants.tokenAlreadyRegistered')),403);
    }
    
});

Route::filter('safetyManualSectionAccess', function($route){
    
    $sectionId = $route->getParameter('sectionId');
    $section = SafetyManualSection::find($sectionId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$section instanceof SafetyManualSection) {
        return Response::make(NULL, 404);
    }
    
    //if section belongs to another group
    if ($section->safetyManual->company_id != $worker->company_id){
        return Response::make(NULL, 403);
    }
});

Route::filter('safetyManualSubsectionAccess', function($route){
    
    $subsectionId = $route->getParameter('subsectionId');
    $subsection = SafetyManualSubsection::find($subsectionId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$subsection instanceof SafetyManualSubsection) {
        return Response::make(NULL, 404);
    }
    
    //if subsection belongs to another group
    if ($subsection->safetyManual->company_id != $worker->company_id){
        return Response::make(NULL, 403);
    }
});


Route::filter('vehicleAccess', function($route){
    
    $vehicleId = $route->getParameter('vehicleId');
    $vehicle = Vehicle::find($vehicleId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    //if vehicle not found
    if (!$vehicle instanceof Vehicle) {
        return Response::make(NULL,404);
    }
    
    //if vehicle belongs to another group
    if ($vehicle->company_id != $worker->company_id){
        return Response::make(NULL,403);
    }
});


Route::filter('inspectionAccess', function($route){
    
    $inspectionId = $route->getParameter('inspectionId');
    $inspection = Inspection::find($inspectionId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    //if vehicle not found
    if (!$inspection instanceof Inspection) {
        return Response::make(NULL,404);
    }
    
    //if vehicle belongs to another group
    if ($inspection->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});


Route::filter('hazardAccess', function($route){
    
    $hazardId = $route->getParameter('hazardId');
    $hazard = Hazard::find($hazardId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$hazard instanceof Hazard) {
        return Response::make(NULL,404);
    }
    
    if ($hazard->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});

Route::filter('nearMissAccess', function($route){
    
    $nearMissId = $route->getParameter('nearMissId');
    $nearMiss = NearMiss::find($nearMissId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$nearMiss instanceof NearMiss) {
        return Response::make(NULL,404);
    }
    
    if ($nearMiss->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});



Route::filter('positiveObservationAccess', function($route){
    
    $positiveObservationId = $route->getParameter('positiveObservationId');
    $positiveObservation = PositiveObservation::find($positiveObservationId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$positiveObservation instanceof PositiveObservation) {
        return Response::make(NULL,404);
    }
    
    if ($positiveObservation->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});

Route::filter('journeyAddressAccess', function($route){
    
    $locationId = $route->getParameter('addressId');
    $location = JourneyLocation::find($locationId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$location instanceof JourneyLocation) {
        return Response::make(NULL,404);
    }
    
    if ($location->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});


Route::filter('flhaAccess', function($route){
    
    $flhaId = $route->getParameter('flhaId');
    $flha = Flha::find($flhaId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$flha instanceof Flha) {
        return Response::make(NULL,404);
    }
    
    if ($flha->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});

Route::filter('tailgateAccess', function($route){
    
    $tailgateId = $route->getParameter('tailgateId');
    $tailgate = Tailgate::find($tailgateId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$tailgate instanceof Tailgate) {
        return Response::make(NULL,404);
    }
    
    if ($tailgate->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});


Route::filter('flhaTaskAccess', function($route){
    
    $flhaTaskId = $route->getParameter('flhaTaskId');
    $flhaTask = FlhaTask::find($flhaTaskId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$flhaTask instanceof FlhaTask) {
        return Response::make(NULL,404);
    }

    if ($flhaTask->flha instanceof Flha){
        if ($flhaTask->flha->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
   
});


Route::filter('tailgateTaskAccess', function($route){
    
    $tailgateTaskId = $route->getParameter('tailgateTaskId');
    $tailgateTask = TailgateTask::find($tailgateTaskId);

    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$tailgateTask instanceof TailgateTask) {
        return Response::make(NULL,404);
    }
  
    if ($tailgateTask->tailgate instanceof Tailgate){
        if ($tailgateTask->tailgate->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
   
});

Route::filter('flhaTaskHazardAccess', function($route){
    
    $flhaTaskHazardId = $route->getParameter('flhaTaskHazardId');
    $flhaTaskHazard = FlhaTaskHazard::find($flhaTaskHazardId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$flhaTaskHazard instanceof FlhaTaskHazard) {
        return Response::make(NULL,404);
    }

    if ($flhaTaskHazard->flhaTask->flha->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});


Route::filter('spotcheckAccess', function($route){
    
    $flhaSpotcheckId = $route->getParameter('flhaSpotcheckId');
    $spotcheck = Spotcheck::find($flhaSpotcheckId);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$spotcheck instanceof Spotcheck) {
        return Response::make(NULL,404);
    }
    
    if ($spotcheck->flha instanceof Flha){
        if ($spotcheck->flha->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
   
});


Route::filter('signoffVisitorAccess', function($route){
    
    $visitorId = $route->getParameter('visitorId');
    $signoffVisitor = SignoffVisitor::find($visitorId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$signoffVisitor instanceof SignoffVisitor) {
        return Response::make(NULL,404);
    }

    if ($signoffVisitor->flha instanceof Flha){
        if ($signoffVisitor->flha->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
   
});


Route::filter('signoffWorkerAccess', function($route){
    
    $workerId = $route->getParameter('workerId');
    $signoffWorker = SignoffWorker::find($workerId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$signoffWorker instanceof SignoffWorker) {
        return Response::make(NULL,404);
    }

    if ($signoffWorker->flha instanceof Flha){
        if ($signoffWorker->flha->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
   
});

Route::filter('breakAccess', function($route){
    
    $breakId = $route->getParameter('breakId');
    $signoffBreak = SignoffBreak::find($breakId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$signoffBreak instanceof SignoffBreak) {
        return Response::make(NULL,404);
    }

    if ($signoffBreak->signoffWorker->flha instanceof Flha){
        if ($signoffBreak->signoffWorker->flha->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
    
});
   
Route::filter('incidentAccess', function($route){
    
    $incidentId = $route->getParameter('incidentId');
    $incident = Incident::find($incidentId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$incident instanceof Incident) {
        return Response::make(NULL,404);
    }

    if ($incident->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
   
});

Route::filter('incidentPersonAccess', function($route){
    
    $incidentPersonId = $route->getParameter('incidentPersonId');
    $incidentPerson = IncidentPerson::find($incidentPersonId);

    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$incidentPerson instanceof IncidentPerson) {
        return Response::make(NULL,404);
    }elseif ($incidentPerson->incident->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
   
});

Route::filter('incidentStatementAccess', function($route){
    
    $incidentStatementId = $route->getParameter('incidentStatementId');
    $incidentStatement = IncidentPartStatement::find($incidentStatementId);

    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$incidentStatement instanceof IncidentPartStatement) {
        return Response::make(NULL,404);
    }elseif ($incidentStatement->incident->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
   
});

Route::filter('incidentMVDAccess', function($route){
    
    $incidentMVDId = $route->getParameter('incidentMVDId');
    $incidentMVD = IncidentMVD::find($incidentMVDId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$incidentMVD instanceof IncidentMVD) {
        return Response::make(NULL,404);
    }elseif ($incidentMVD->incident->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
   
});

Route::filter('incidentMTAccess', function($route){
    
    $incidentTreatmentId = $route->getParameter('incidentTreatmentId');
    $incidentTreatment = IncidentTreatment::find($incidentTreatmentId);
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    if (!$incidentTreatment instanceof IncidentTreatment) {
        return Response::make(NULL,404);
    }elseif ($incidentTreatment->incident->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
   
});


Route::filter('hasSafetyManual', function($route){
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    if(!$worker->company->safetyManual){
        return Response::make(NULL,404);
    }
});

Route::filter('safetyManualPhotoAccess', function($route){
    $photoName = $route->getParameter('photoId');
    $photo = Photo::getPhotoByPhotoName($photoName);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    //if photo not found
    if (!$photo instanceof Photo) {
        return Response::make(NULL,404);
    }
    //get the object that the image is attached to
    $imageBelongsTo = $photo->imageable;
      
    if ($imageBelongsTo instanceof SafetyManual){
        if ($imageBelongsTo->company_id == $worker->company_id){
           // For some reason I can't get != working. 
        } else {
            return Response::make(NULL,403);
        }
    } 
});

Route::filter('photoAccess', function($route){
//    dd(1);
    $photoName = $route->getParameter('photoId');
    $photo = Photo::getPhotoByPhotoName($photoName);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    //if photo not found
    if (!$photo instanceof Photo) {
        return Response::make(NULL,404);
    }
    //get the object that the image is attached to
    $imageBelongsTo = $photo->imageable;

    if ($imageBelongsTo instanceof Vehicle){
        //if it's a vehicle, all workers in group have access
        if ($imageBelongsTo->company_id != $worker->company_id){
            return Response::make(NULL,403);
        }
    } else {
        //if it's not a vehicle, only the worker that added the image has access
        if ($photo->worker_id != $worker->worker_id){
            return Response::make(NULL,403);
        }
    }
});

Route::filter('ticketPhotoAccess', function($route){
    $photoName = $route->getParameter('photoId');
    $photo = Photo::getPhotoByPhotoName($photoName);
    
    $worker = Worker::getByAuthToken(Input::get('authToken'));
    
    //if photo not found
    if (!$photo instanceof Photo) {
        return Response::make(NULL,404);
    }
    //get the object that the image is attached to
    $imageBelongsTo = $photo->imageable;

    if (!$imageBelongsTo instanceof Ticket || $photo->worker_id != $worker->worker_id){
        return Response::make(NULL,403);
    }
});

Route::filter('ticketApprovalAccess', function($route){
    $adminId = $route->getParameter('adminId');
    $approver = Admin::find($adminId);
    if($approver) {
        $worker = Worker::getByAuthToken(Input::get('authToken'));

        // Check that the worker is in the same company as the approver
        if(!$worker->company_id == $approver->company_id){
            return Response::make(NULL,403);
        }
    } else {
        return Response::make(NULL,404);
    }
});


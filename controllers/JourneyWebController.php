<?php

class JourneyWebController extends AjaxController {

    public function journeysView($journeyId = NULL) {
        $loggedUser = Auth::user();
        $data['journeys'] = JourneyV2::getForCompany($loggedUser);
        $journeyId = JourneyV2::find($journeyId);
        $data['journey'] = $journeyId;
        $data['user'] = $loggedUser;
        return View::make('webApp::journeys.list', $data);
    }

    public function journeyDetailsView($journeyId){      

        $loggedUser = Auth::user();
        $data['journey'] = JourneyV2::find($journeyId);//JourneyV2::getForCompany($loggedUser);

        if (!Auth::user()->isAdmin() && $data['journey']->addedBy->company_id != Auth::user()->company_id) {
            return Redirect::to('/');
        }

        $journeyId = JourneyV2::find($journeyId);
        $data['user'] = $loggedUser;
        return View::make('webApp::journeys.view', $data);
    }

    public function deleteJourneyAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $journeyID = str_replace('delete_', '', Input::get('delete_journey_id', null));
        $journey = JourneyV2::find($journeyID);      

        // The journey table does not have a company_id so we find it using the worker who added it
        $workerID = $journey->worker_id;
        $worker = Worker::find($workerID);
        $journeyCompanyID = $worker->company_id; 

        if($loggedUser->company_id == $journeyCompanyID || $loggedUser->isAdmin()) {
            $journey->deleted_at = date('Y-m-d H:i:s');
            $journey->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }

     /*
    // This is unused
    public function getJourneyDetailsAction($journeyId) {
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $journey = Journey::find($journeyId);

        // This check may not work as expected. Test to ensure that users cannot view other companies' journeys.
        if (!Auth::user()->isAdmin() && $journey->addedBy->company_id != Auth::user()->company_id) {
            return Redirect::to('/');
        }
        $journey->load('addedBy', 'journeyFrom', 'journeyTo', 'checkins');
        $journey->expectedHalftime = $journey->expectedHalftimeCheckinTime();
        $journey->expectedFulltime = $journey->expectedFulltimeCheckinTime();
        $journey->needsHalftime = $journey->requiresHalftimeCheckin();
        $journey->needsFulltime = $journey->requiresFulltimeCheckin();
        $journey->hasHalftime = $journey->hasHalftimeCheckin();
        $journey->hasFulltime = $journey->hasFulltimeCheckin();
        return $journey;//self::buildAjaxResponse(TRUE, $journey);
    }*/

}
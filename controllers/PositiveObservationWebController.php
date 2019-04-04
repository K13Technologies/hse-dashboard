<?php

class PositiveObservationWebController extends AjaxController {
    
    public function positiveObservationsView(){
        $loggedUser = Auth::user();
        $data['pos'] = PositiveObservation::getForCompany($loggedUser);
//        $data['pos'] = Hazard::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        return View::make('webApp::positive-observations.list',$data);
    }
    
    public function positiveObservationDetailsView($positiveObservationId){
        $po = PositiveObservation::find($positiveObservationId);
        if (!Auth::user()->isAdmin() && $po->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['po'] = $po;
        return View::make('webApp::positive-observations.view',$data);
    }
    
    public function positiveObservationDetailsExport($positiveObservationId){
        $po = PositiveObservation::find($positiveObservationId);
        $data['po'] = $po;
//        return View::make('webApp::positive-observations.export',$data);
//        $html = View::make('webApp::positive-observations.export',$data);
        $html = View::make('webApp::positive-observations.export2',$data)->render();

        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $po->title." ".$po->created_at.'.pdf';
        return $pdf->download($filename);
    }
    
    public function positiveObservationDetailsMail(){
        $positiveObservationId = Input::get('positive_observation_id');
        $email = Input::get('email');
        
        $po = PositiveObservation::find($positiveObservationId);
        if (!Auth::user()->isAdmin() && $po->addedBy->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP has been logged.');
        }
        $data['po'] = $po;
        $html = View::make('webApp::positive-observations.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
       
        $filename = StorageManager::saveTempPO($pdf,$po);
        
        $data = array('email'=>$email,
                      'type' =>'Field Observation Card',
                      'object' =>$po,
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);
        
    }
    
    public function addAction(){
        $poId = Input::get('positive_observation_id');
        $completedOn = Input::get('completed_on','');
        $actionDescription = Input::get('action','');
        $po = PositiveObservation::find($poId);
        
        if ($completedOn == ''){
            $po->action = $actionDescription;
            $po->completed_on = NULL;
        }else{
            if (date(strtotime($completedOn))){
              $po->completed_on = $completedOn;
            }
        }
        $po->save();
        return self::buildAjaxResponse(TRUE, TRUE);
    }

    // This functionality should NOT be in the controller.
    // Don't know how to implement a better architecture right now.
    public function validateWebForm($input){
        $rules = array( 'title' => 'required|max:100',
                        'site' => 'required|max:100|',
                        'specific_location' => 'max:100',
                        'lsd' => 'regex:/\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM/|max:30',
                        'wellpad' => 'max:100',
                        'personsObserved'=> 'required',
                        'categoryIds' => 'required',
                        'comment' => 'max:400',
                        'positive_observation_activity_id' => 'required',
                        'task_1_title' => 'required|max:100',
                        'task_1_description' => 'required|max:400',
                        'is_po_details' => 'required|max:400',
                        'completed_on' => 'regex:/\d\d\d\d-\d\d-\d\d/',
                        'action' => 'max:500'
                    );

        // In the future, we should also be checking that the personsObserved[][name] and [company] fields are set and ax length of 100

        //Tasks Observed checking (2 and 3 are optional)
        if(isset($input['task_2_title']) || isset($input['task_2_description'])) {
            $rules['task_2_title'] = 'required|max:100';
            $rules['task_2_description'] = 'required|max:400';
        }
        if(isset($input['task_3_title']) || isset($input['task_3_description'])) {
            $rules['task_3_title'] = 'required|max:100';
            $rules['task_3_description'] = 'required|max:400';
        }

        $validator = Validator::make($input, $rules);

        // Examples of other rules
        //, |unique:companies,company_name',
        //'email' => 'required|email|unique:admins,email'

        if ($validator->fails()){
            // Markup can be placed in the :message area
            return array('result'=>FALSE, 'error'=>$validator->messages()->all(':message'));
        }

        return array('result'=>TRUE);
    } 

    public function editObservationAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::all();

        $observationActivityID = Input::get('positive_observation_activity_id', NULL);
        $observationID = $input['positive_observation_id'];
        $observation = PositiveObservation::find($observationID);        
        $observationCompanyID = PositiveObservation::getCompanyID($observation->positive_observation_id);
        // Pull the value out of the JSON
        $observationCompanyID = $observationCompanyID['company_id'];

        /* SERVER SIDE VALIDATION */
        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }
        /* END SERVER SIDE VALIDATION */


        if($loggedUser->company_id == $observationCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($observation, $input){

                $observation->setWebFields($input);
                $observation->setCategories($input);
                $observation->setWebPersonsObserved($input);
                $observation->save();
            });

            if ($error == NULL) {
                return self::buildAjaxResponse(TRUE, 'Edit Successful.'); 
            } else {
                return self::buildAjaxResponse(FALSE, 'Edit Failed. Please try again. If the problem persists, please contact White Knight Support.');
            }
        } else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }
    
    public function deleteObservationAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $observationID = str_replace('delete_', '', Input::get('delete_observation_id', null));
        $observation = PositiveObservation::getByID($observationID);     

        $observationCompanyID = PositiveObservation::getCompanyID($observation->positive_observation_id);
        // Pull the value out of the JSON
        $observationCompanyID = $observationCompanyID['company_id'];  

        if($loggedUser->company_id == $observationCompanyID || $loggedUser->isAdmin()) {
            $observation->deleted_at = date('Y-m-d H:i:s');
            $observation->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }
    
}
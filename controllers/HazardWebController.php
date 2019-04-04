<?php

class HazardWebController extends AjaxController {
    
    public function hazardCardsView(){
        $loggedUser = Auth::user();
        $data['hazards'] = Hazard::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        return View::make('webApp::hazard-cards.list',$data);
    }
    
    public function hazardCardDetailsView($hazardId){
        $hazard = Hazard::find($hazardId);
        if (!Auth::user()->isAdmin() && $hazard->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['hazard'] = $hazard;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();
        return View::make('webApp::hazard-cards.view',$data);
    }
    
    public function hazardCardDetailsExport($hazardId){
        $hazard = Hazard::find($hazardId);
        if (!Auth::user()->isAdmin() && $hazard->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['hazard'] = $hazard;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();

        $html = View::make('webApp::hazard-cards.export2', $data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $hazard->title." ".$hazard->created_at.'.pdf';
        return $pdf->download($filename);
    }
    
    public function hazardCardDetailsMail(){
        $hazardId = Input::get('hazard_id');
        $email = Input::get('email');
        
        $hazard = Hazard::find($hazardId);
        if (!Auth::user()->isAdmin() && $hazard->addedBy->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $data['hazard'] = $hazard;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();

        $html = View::make('webApp::hazard-cards.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
       
        $filename = StorageManager::saveTempHazard($pdf,$hazard);
        
        $data = array('email'=>$email,
                      'object'=>$hazard,
                      'type' =>'Hazard Card',
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);
        
    }
    
    
    public function addAction(){
        $hazardId = Input::get('hazard_id');
        $completedOn = Input::get('completed_on','');
        $actionDescription = Input::get('action','');
        $hazard = Hazard::find($hazardId);
        
        if ($completedOn == ''){
            $hazard->action = $actionDescription;
            $hazard->completed_on = NULL;
        }else{
            if (date(strtotime($completedOn))){
              $hazard->completed_on = $completedOn;
            }
        }
        $hazard->save();
        return self::buildAjaxResponse(TRUE, TRUE);
    }

    public function deleteHazardAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $hazardID = str_replace('delete_', '', Input::get('delete_hazard_id', null));
        $hazard = Hazard::getByID($hazardID);      

        $hazardCompanyID = Hazard::getCompanyID($hazard->hazard_id);
        // Pull the value out of the JSON
        $hazardCompanyID = $hazardCompanyID['company_id'];

        // Test printout
        //die('hazardID: ' . $hazardID.' company ID: '.$hazardCompanyID .$hazard);  

        if($loggedUser->company_id == $hazardCompanyID || $loggedUser->isAdmin()) {
            $hazard->deleted_at = date('Y-m-d H:i:s');//
            $hazard->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }

    // This functionality should NOT be in the controller.
    // Don't know how to implement a better architecture right now.
    public function validateWebForm($input){
        $rules = array( 'title' => 'required|max:100',
                        'site' => 'required|max:100|',
                        'specific_location' => 'max:100',
                        'lsd' => 'regex:/\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM/|max:30',
                        'wellpad' => 'max:100',
                        'road' => 'max:100',
                        'description' => 'required|max:400',
                        'hazard_category_ids' => 'required',
                        'corrective_action' => 'required|max:400',
                        'comment' => 'max:400'
                    );

        // If the corrective action was NOT applied they must fill out the reason why
        if (!isset($input['corrective_action_applied'])) {
            $rules['corrective_action_implementation'] = 'required|max:400';
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

    // Called from the web app
    public function editHazardAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::all();

        $hazardActivityID = Input::get('hazard_activity_id', NULL);
        $hazardID = $input['hazard_id'];
        $hazard = Hazard::getByID($hazardID);        
        $hazardCompanyID = Hazard::getCompanyID($hazard->hazard_id);
        // Pull the value out of the JSON
        $hazardCompanyID = $hazardCompanyID['company_id'];

        
        /* SERVER SIDE VALIDATION */
        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }
        /* END SERVER SIDE VALIDATION */

        if($loggedUser->company_id == $hazardCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($hazard, $input){
                $hazard->setWebFormFields($input);
                $hazard->setHazardCategories($input);
                $hazard->save();
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
    
}
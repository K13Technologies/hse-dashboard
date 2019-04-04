<?php

class NearMissWebController extends AjaxController {

    /*
     * this would be the first step in implementing repositories -- something we should possibly consider in the future
     *
     * public function __construct (SafetyCategoryRepository $category) {
     *   $this->category = $category;
    }*/
    
    public function nearMissesView(){
        $loggedUser = Auth::user();
        $data['nearMisses'] = NearMiss::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        return View::make('webApp::near-misses.list',$data);
    }
    
    public function nearMissDetailsView($nearMissId){
        $nearMiss = NearMiss::find($nearMissId);
        if (!Auth::user()->isAdmin() && $nearMiss->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['nearMiss'] = $nearMiss;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();
        return View::make('webApp::near-misses.view',$data);
    }
    
    public function nearMissDetailsExport($nearMissId){
        $nearMiss = NearMiss::find($nearMissId);
        $data['nearMiss'] = $nearMiss;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();
        $html = View::make('webApp::near-misses.export2',$data)->render();
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $nearMiss->title." ".$nearMiss->created_at.'.pdf';
        return $pdf->download($filename);
    }
    
    public function nearMissDetailsMail(){
        $nearMissId = Input::get('near_miss_id');
        $email = Input::get('email');
        
        $nearMiss = NearMiss::find($nearMissId);
        if (!Auth::user()->isAdmin() && $nearMiss->addedBy->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $data['nearMiss'] = $nearMiss;
        $data['hazardActivities'] = HazardActivity::all();
        $data['hazardCategories'] = HazardCategory::all();
        $html = View::make('webApp::near-misses.export2',$data)->render();
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = StorageManager::saveTempNearMiss($pdf,$nearMiss);
        $data = array('email'=>$email,
                      'type' =>'Near Miss Card',
                      'object' =>$nearMiss,
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);
    }
    
    public function addAction(){
        $nearMissId = Input::get('near_miss_id');
        $completedOn = Input::get('completed_on','');
        $actionDescription = Input::get('action','');
        $nearMiss = NearMiss::find($nearMissId);
        
        if ($completedOn == ''){
            $nearMiss->action = $actionDescription;
            $nearMiss->completed_on = NULL;
        }else{
            if (date(strtotime($completedOn))){
              $nearMiss->completed_on = $completedOn;
            }
        }
        $nearMiss->save();
        return self::buildAjaxResponse(TRUE, TRUE);
    }

    public function deleteNearMissAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $nearMissID = str_replace('delete_', '', Input::get('delete_near_miss_id', null));
        $nearMiss = NearMiss::getByID($nearMissID);        

        $nearMissCompanyID = NearMiss::getCompanyID($nearMiss->near_miss_id);
        // Pull the value out of the JSON
        $nearMissCompanyID = $nearMissCompanyID['company_id'];

        if($loggedUser->company_id == $nearMissCompanyID || $loggedUser->isAdmin()) {
            $nearMiss->deleted_at = date('Y-m-d H:i:s');//
            $nearMiss->save();

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
    public function editNearMissAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::all();

        $hazardActivityID = Input::get('hazard_activity_id', NULL);
        $nearMissID = $input['near_miss_id'];
        $nearMiss = NearMiss::getByID($nearMissID);        
        $nearMissCompanyID = NearMiss::getCompanyID($nearMiss->near_miss_id);
        // Pull the value out of the JSON
        $nearMissCompanyID = $nearMissCompanyID['company_id'];

        
        /* SERVER SIDE VALIDATION */
        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }
        /* END SERVER SIDE VALIDATION */

        if($loggedUser->company_id == $nearMissCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($nearMiss, $input){
                $nearMiss->setWebFormFields($input);
                $nearMiss->setHazardCategories($input);
                $nearMiss->save();
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
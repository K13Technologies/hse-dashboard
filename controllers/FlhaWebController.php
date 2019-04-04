<?php

class FlhaWebController extends AjaxController {
    
    public function flhasView(){
        $loggedUser = Auth::user();
        $data['flhas'] = Flha::getForCompany($loggedUser);
        return View::make('webApp::flha.list',$data);
    }
    
    public function flhaDetailsAction($flhaId){
        $flha = Flha::getCompleteWithFullDetails($flhaId);
        if (!Auth::user()->isAdmin() && $flha->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['flha'] = $flha;
        return View::make('webApp::flha.view',$data);
    }
    
    
    public function flhaDetailsExport($flhaId){
        $flha = Flha::getCompleteWithFullDetails($flhaId);
        if (!Auth::user()->isAdmin() && $flha->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['flha'] = $flha;
        $data['sm'] = new StorageManager;
//        return View::make('webApp::flha.export',$data);
        
        $html = View::make('webApp::flha.export2',$data)->render();

        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $flha->title." ".$flha->created_at.'.pdf';
        return $pdf->download($filename);
    }
    
    public function flhaDetailsExportMail(){
        $flhaId = Input::get('flha_id');
        $email = Input::get('email');
        
        $flha = Flha::getCompleteWithFullDetails($flhaId);
        if (!Auth::user()->isAdmin() && $flha->addedBy->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $data['flha'] = $flha;
        $data['sm'] = new StorageManager;
        $html = View::make('webApp::flha.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
       
        $filename = StorageManager::saveTempFlha($pdf,$flha);
        
        $data = array('email'=>$email,
                      'object'=>$flha,
                      'type' =>'FLHA Card',
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);
        
    }
    
    
    public function getVisitorDetailsAction($visitorId){
        $visitor = SignoffVisitor::find($visitorId);
        $sm = new StorageManager();
        $photo = $sm->getSignoffVisitorPhoto($visitor);
        $signature = $sm->getSignoffVisitorSignature($visitor);
        $hasPhoto = is_file($photo);
        $hasSignature = is_file($signature);
        $visitor->photo = $hasPhoto;
        $visitor->signature = $hasSignature;
        if (!Auth::user()->isAdmin() && $visitor->flha->addedBy->company_id != Auth::user()->company_id){
            return self::buildAjaxResponse(false);
        }
        unset($visitor->flha);
        return self::buildAjaxResponse(TRUE, $visitor);
    }
    
    
    
    public function getWorkerDetailsAction($workerId){
        $worker = SignoffWorker::with('breaks')->find($workerId);
        $sm = new StorageManager();
        $photo = $sm->getSignoffWorkerPhoto($worker);
        $signature = $sm->getSignoffWorkerSignature($worker);
        $hasPhoto = is_file($photo);
        $hasSignature = is_file($signature);
        $worker->photo = $hasPhoto;
        $worker->signature = $hasSignature;
        if (!Auth::user()->isAdmin() && $worker->flha->addedBy->company_id != Auth::user()->company_id){
            return self::buildAjaxResponse(false);
        }
        unset($worker->flha);
        return self::buildAjaxResponse(TRUE, $worker);
    }
    
    
    public function getSpotcheckDetailsAction($spotcheckId){
        $spotcheck = Spotcheck::find($spotcheckId);
        $sm = new StorageManager();
        $signature = $sm->getSpotcheckSignaturePhoto($spotcheck);
        $hasSignature = is_file($signature);
        $spotcheck->signature = $hasSignature;
        $spotcheck->created_at = WKSSDate::display($spotcheck->ts, $spotcheck->created_at);
        if (!Auth::user()->isAdmin() && $spotcheck->flha->addedBy->company_id != Auth::user()->company_id){
            return self::buildAjaxResponse(false);
        }
        unset($spotcheck->flha);
        return self::buildAjaxResponse(TRUE, $spotcheck);
    }

    public function flhaDeleteAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $flhaID = str_replace('delete_', '', Input::get('delete_flha_id', null));
        $flha = Flha::getByID($flhaID);      

        $flhaCompanyID = Flha::getCompanyID($flha->flha_id);
        // Pull the value out of the JSON
        $flhaCompanyID = $flhaCompanyID['company_id']; 

        if($loggedUser->company_id == $flhaCompanyID || $loggedUser->isAdmin()) {
            $flha->deleted_at = date('Y-m-d H:i:s');//
            $flha->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }




    // This functionality should NOT be in the controller.
    // Don't know how to implement a better architecture right now.
    private function validateWebForm($input){
        $rules = array( 'title' => 'required|max:100',
                        'job_description' => 'required|max:400',
                        'job_number' => 'max:100',
                        'client' => 'required|max:100',
                        'phone_number' => 'max:100',
                        'stars_site' => 'max:100',
                        'tasks' => 'required',
                        'supervisor_name' => 'required|max:100',
                        'supervisor_number' => 'required|regex:/\d\d\d-\d\d\d-\d\d\d\d/',
                        'locations.0' => 'required',
                        'muster_point' => 'required|max:100'
                       );

        $messages = array();

        if (isset($input['tasks'])) {
            $taskCount = 0;
            foreach ($input['tasks'] as $task) {
                $rules['tasks.' . $taskCount . '.title'] = 'required|max:100';
                
                if (isset($task['hazards'])) 
                {
                    $hazardCount = 0;
                    foreach($task['hazards'] as $hazard) {
                        $description = 'tasks.' . $taskCount . '.hazards.' . $hazardCount . '.description';
                        $eliminate_hazard = 'tasks.' . $taskCount . '.hazards.' . $hazardCount . '.eliminate_hazard';

                        $rules[$description] = 'required|max:100';
                        $rules[$eliminate_hazard] = 'required|max:100';

                        $messageNameDescription = $description . '.required';
                        $messageNameEliminateHazard = $eliminate_hazard . '.required';

                        $readableTaskNumber = $taskCount + 1;

                        $messages[$messageNameDescription] = 'You must fill out the Hazard Details field in your Task #'.$readableTaskNumber;
                        $messages[$messageNameEliminateHazard] ='You must fill out the Eliminate/Control Hazard field in your Task #'.$readableTaskNumber;
                        $hazardCount++;
                    }
                }
                
                $taskCount++;
            }
        } else {
            return array('result'=>FALSE, 'error'=>'At least one task is required.');
        }

        // if gloves had to be removed, enforce required and length rule
        // if working alone, enforce required and length rule
        // if warning ribbon needed, enforce required and length rule

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()){
            // Markup can be placed in the :message area should we ever change the display format
            return array('result'=>FALSE, 'error'=>$validator->messages()->all(':message'));
        }

        return array('result'=>TRUE);
    } 

    // Helper function which removes the user specified tasks from the DB
    private function removeDeletedTasks ($flha, $newCurrentTasks) {
        $oldTasks = $flha->tasks()->get()->all();
        $oldTaskIds = array();
        foreach ($oldTasks as $oldTask) {
            $oldTaskID = $oldTask->flha_task_id;
            array_push($oldTaskIds,$oldTaskID);
        }

        // array_diff This is used to get tasks which are still in the db which are not in the input. We will delete those tasks.
        $deletableTaskIDs = array_diff($oldTaskIds, $newCurrentTasks);

        foreach ($deletableTaskIDs as $taskID) {
            $flhaTask = FlhaTask::getWithHazardListById($taskID);
            $flhaTask->delete();
        }
    }

    private function editFlhaTasks($input, $flha){        
        $newCurrentTasks = array(); // Used to keep track of the new set of tasks so that later we can determine which tasks in the DB need to be deleted
        foreach($input['tasks'] as $task) 
        {
            $flhaTask = FlhaTask::firstOrNew(array('flha_task_id'=>$task['flha_task_id'], 'flha_id'=>$input['flha_id']));
            $flhaTask->setFields($task);

            if ($flhaTask->save()) 
            {
                $newCurrentHazardsForTask = array(); // Keeps track of new set of hazards so that we can determine which hazards need to be removed from DB
                // If there are any hazards associated with the task
                if(isset($task['hazards'])) {
                    foreach($task['hazards'] as $inputtedHazard)
                    {
                        $hazardID = Self::editFlhaTaskHazard($inputtedHazard, $flhaTask->flha_task_id);

                        if($hazardID) {
                            array_push($newCurrentHazardsForTask, $hazardID);
                        }
                    }
                    // New and existing hazards have been added and edited --  now time to delete any tasks that the user may have deleted.
                    Self::removeDeletedTaskHazards($flhaTask, $newCurrentHazardsForTask);
                } else {
                    // This is the case in which a user deletes all the hazards from a task
                    Self::deleteAllHazardsForTask($flhaTask);
                }
            }

            array_push($newCurrentTasks, $flhaTask->flha_task_id);
        }

        // New and existing tasks have been added and edited --  now time to delete any tasks that the user may have deleted.
        Self::removeDeletedTasks($flha, $newCurrentTasks);  
    }

    private function deleteAllHazardsForTask($flhaTask) {
        $deletableHazards = $flhaTask->hazards()->get()->all();

        foreach ($deletableHazards as $hazard) {
            $hazard->delete();
        }
    }

    // Removes the user specified task hazards from the DB
    private function removeDeletedTaskHazards ($flhaTask, $newCurrentHazardsForTask) {
        $oldHazards = $flhaTask->hazards()->get()->all();
        $oldHazardIds = array();
        foreach ($oldHazards as $oldHazard) {
            $oldHazardID = $oldHazard->flha_task_hazard_id;
            array_push($oldHazardIds,$oldHazardID);
        }

        // array_diff is used to get hazards which are still in the db which are not in the input. We will delete those hazards.
        $deletableHazardIDs = array_diff($oldHazardIds, $newCurrentHazardsForTask);

        foreach ($deletableHazardIDs as $hazardID) {
            $taskHazard = FlhaTaskHazard::find($hazardID);
            $taskHazard->delete();
        }
    }

    // Edits or creates a new task hazard with user input
    private function editFlhaTaskHazard($userEditedHazard, $flha_task_id){  
        // Try to find the existing hazard or create a new one 
        $hazard = FlhaTaskHazard::firstOrNew(array('flha_task_hazard_id'=>$userEditedHazard['flha_task_hazard_id'], 'flha_task_id'=>$flha_task_id));    
        $hazard->setFields($userEditedHazard);
        
        if($hazard->save()){
            return $hazard->flha_task_hazard_id;
        } 
    }

    private function editFlhaJobCompletionFields ($input, $flha) {
        if ($flha->completion instanceOf JobCompletion) {
            $completion = $flha->completion;
            $completion->setFields($input);
            $completion->save();
        }
    }

    public function editFlhaAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::json()->all();
        $flhaID = $input['flha_id'];

        $flha = Flha::find($flhaID);        
        $flhaCompanyID = Flha::getCompanyID($flha->flha_id);
        // Pull the value out of the JSON
        $flhaCompanyID = $flhaCompanyID['company_id'];

        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }

        if($loggedUser->company_id == $flhaCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($flha, $input){

                $flha->setFields($input);

                if(isset($input['locations'])) { 
                    $flha->setWebLocations($input['locations']);
                }

                if(isset($input['lsds'])) {
                    $flha->setWebLSDs($input['lsds']);
                }

                if(isset($input['sites'])) {
                    $flha->setWebFormSites($input['sites']);
                }
                
                if(isset($input['permitNumbers'])) {
                    $flha->setWebPermits($input['permitNumbers']);
                }

                Self::editFlhaTasks($input, $flha);
                Self::editFlhaJobCompletionFields($input, $flha);

                $flha->save();
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
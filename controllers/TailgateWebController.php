<?php

class TailgateWebController extends AjaxController {
    
    public function tailgatesView(){
        $loggedUser = Auth::user();
        $data['tailgates'] = Tailgate::getForCompany($loggedUser);
        return View::make('webApp::tailgate.list',$data);
    }
    
    public function tailgateDetailsAction($tailgateId){
        $tailgate = Tailgate::getCompleteWithFullDetails($tailgateId);
        if (!Auth::user()->isAdmin() && $tailgate->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['tailgate'] = $tailgate;
        return View::make('webApp::tailgate.view',$data);
    }
    
    
    public function tailgateDetailsExport($tailgateId){
        $tailgate = Tailgate::getCompleteWithFullDetails($tailgateId);
        if (!Auth::user()->isAdmin() && $tailgate->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $data['tailgate'] = $tailgate;
        $data['sm'] = new StorageManager;
		//return View::make('webApp::flha.export',$data);
        
        $html = View::make('webApp::tailgate.export2',$data)->render();

        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $tailgate->title." ".$tailgate->created_at.'.pdf';
        return $pdf->download($filename);
    }
    
    public function tailgateDetailsExportMail(){
        $tailgateId = Input::get('tailgate_id');
        $email = Input::get('email');
        
        $tailgate = Tailgate::getCompleteWithFullDetails($tailgateId);
        if (!Auth::user()->isAdmin() && $tailgate->addedBy->company_id != Auth::user()->company_id){
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $data['tailgate'] = $tailgate;
        $data['sm'] = new StorageManager;
        $html = View::make('webApp::tailgate.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
       
        $filename = StorageManager::saveTempTailgate($pdf,$tailgate);
        
        $data = array('email'=>$email,
                      'object'=>$tailgate,
                      'type' =>'Tailgate Card',
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);   
    }
    
    public function getVisitorDetailsAction($visitorId){
        $visitor = TailgateSignoffVisitor::find($visitorId);
        $sm = new StorageManager();
        $signature = $sm->getTailgateSignoffVisitorSignature($visitor);
        $hasSignature = is_file($signature);
        $visitor->signature = $hasSignature;
        if (!Auth::user()->isAdmin() && $visitor->tailgate->addedBy->company_id != Auth::user()->company_id){
            return self::buildAjaxResponse(false);
        }
        unset($visitor->tailgate);
        return self::buildAjaxResponse(TRUE, $visitor);
    }
     
    public function getWorkerDetailsAction($workerId){
        $worker = TailgateSignoffWorker::find($workerId);
        $sm = new StorageManager();
        $signature = $sm->getTailgateSignoffWorkerSignature($worker);
        $hasSignature = is_file($signature);
        $worker->signature = $hasSignature;
        if (!Auth::user()->isAdmin() && $worker->tailgate->addedBy->company_id != Auth::user()->company_id){
            return self::buildAjaxResponse(false);
        }
        unset($worker->tailgate);
        return self::buildAjaxResponse(TRUE, $worker);
    }

    public function tailgateDeleteAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $tailgateID = str_replace('delete_', '', Input::get('delete_tailgate_id', null));
        $tailgate = Tailgate::getByID($tailgateID);      

        $tailgateCompanyID = Tailgate::getCompanyID($tailgate->tailgate_id);
        // Pull the value out of the JSON
        $tailgateCompanyID = $tailgateCompanyID['company_id']; 

        if($loggedUser->company_id == $tailgateCompanyID || $loggedUser->isAdmin()) {
            $tailgate->deleted_at = date('Y-m-d H:i:s');
            $tailgate->save();

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
                        'phone_number' => 'max:100',
                        'stars_site' => 'max:100',
                        'tasks' => 'required',
                        'supervisors.0' => 'required',
                        'permits.0' => 'required'
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

        // Can still do Assessment Checklist validation here for all descriptions where answer is NO

        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()){
            // Markup can be placed in the :message area should we ever change the display format
            return array('result'=>FALSE, 'error'=>$validator->messages()->all(':message'));
        }

        return array('result'=>TRUE);
    } 

    // Helper function which removes the user specified tasks from the DB
    private function removeDeletedTasks ($tailgate, $newCurrentTasks) {
    	$oldTasks = $tailgate->tasks()->get()->all();
    	$oldTaskIds = array();
    	foreach ($oldTasks as $oldTask) {
    		$oldTaskID = $oldTask->tailgate_task_id;
    		array_push($oldTaskIds,$oldTaskID);
    	}

    	// array_diff This is used to get tasks which are still in the db which are not in the input. We will delete those tasks.
    	$deletableTaskIDs = array_diff($oldTaskIds, $newCurrentTasks);

    	foreach ($deletableTaskIDs as $taskID) {
    		$tailgateTask = TailgateTask::getWithHazardListById($taskID);
    		$tailgateTask->delete();
    	}
    }

    private function editTailgateTasks($input, $tailgate){        
		$newCurrentTasks = array(); // Used to keep track of the new set of tasks so that later we can determine which tasks in the DB need to be deleted

        foreach($input['tasks'] as $task) 
        {
            //tailgate_task_id will be set even if it does not have a value (for new tasks) -- it is a hidden form field in this case
            if (isset($task['tailgate_task_id']) && isset($input['tailgate_id'])) 
            {
                $tailgateTask = TailgateTask::firstOrNew(array('tailgate_task_id'=>$task['tailgate_task_id'], 'tailgate_id'=>$input['tailgate_id']));
                $tailgateTask->setFields($task);

                if ($tailgateTask->save()) 
                {
                	$newCurrentHazardsForTask = array(); // Keeps track of new set of hazards so that we can determine which hazards need to be removed from DB
	                // If there are any hazards associated with the task
	                if(isset($task['hazards'])) {
	                    foreach($task['hazards'] as $inputtedHazard)
	                    {
	                    	$hazardID = Self::editTailgateTaskHazard($inputtedHazard, $tailgateTask->tailgate_task_id);

	                    	if($hazardID) {
	                    		array_push($newCurrentHazardsForTask, $hazardID);
	                    	}
	                    }
	                    // New and existing hazards have been added and edited --  now time to delete any tasks that the user may have deleted.
	                    Self::removeDeletedTaskHazards($tailgateTask, $newCurrentHazardsForTask);
	                } else {
	                	// This is the case in which a user deletes all the hazards from a task
	                	Self::deleteAllHazardsForTask($tailgateTask);
	                }
                }

                array_push($newCurrentTasks, $tailgateTask->tailgate_task_id);
            }
        }

        // New and existing tasks have been added and edited --  now time to delete any tasks that the user may have deleted.
    	Self::removeDeletedTasks($tailgate, $newCurrentTasks);	
    }

    private function deleteAllHazardsForTask($tailgateTask) {
    	$deletableHazards = $tailgateTask->hazards()->get()->all();

    	foreach ($deletableHazards as $hazard) {
    		$hazard->delete();
    	}
    }

    // Removes the user specified task hazards from the DB
    private function removeDeletedTaskHazards ($tailgateTask, $newCurrentHazardsForTask) {
    	$oldHazards = $tailgateTask->hazards()->get()->all();
    	$oldHazardIds = array();
    	foreach ($oldHazards as $oldHazard) {
    		$oldHazardID = $oldHazard->tailgate_task_hazard_id;
    		array_push($oldHazardIds,$oldHazardID);
    	}

    	// array_diff is used to get hazards which are still in the db which are not in the input. We will delete those hazards.
    	$deletableHazardIDs = array_diff($oldHazardIds, $newCurrentHazardsForTask);

    	foreach ($deletableHazardIDs as $hazardID) {
    		$taskHazard = TailgateTaskHazard::find($hazardID);
    		$taskHazard->delete();
    	}
    }

    // Edits or creates a new task hazard with user input
    private function editTailgateTaskHazard($userEditedHazard, $tailgate_task_id){   
    	// Try to find the existing hazard or create a new one 
    	$hazard = TailgateTaskHazard::firstOrNew(array('tailgate_task_hazard_id'=>$userEditedHazard['tailgate_task_hazard_id'], 'tailgate_task_id'=>$tailgate_task_id));    
	    $hazard->setFields($userEditedHazard);
	    
	    if($hazard->save()){
	    	return $hazard->tailgate_task_hazard_id;
	    } 
    }

    private function editTailgateJobCompletionFields ($input, $tailgate) {
    	if ($tailgate->completion instanceOf JobCompletion) {
    		$completion = $tailgate->completion;
    		$completion->setFields($input);
    		$completion->save();
    	}
    }

    public function editTailgateAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::all();

        $tailgateID = $input['tailgate_id'];
        $tailgate = Tailgate::find($tailgateID);        
        $tailgateCompanyID = Tailgate::getCompanyID($tailgate->tailgate_id);
        // Pull the value out of the JSON
        $tailgateCompanyID = $tailgateCompanyID['company_id'];

        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }

        if($loggedUser->company_id == $tailgateCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($tailgate, $input){

                $tailgate->setFields($input);

                if(isset($input['locations'])) { 
                	$tailgate->setLocations($input);
                } else {
                	$tailgate->deleteAllLocations();
                }

                if(isset($input['lsds'])) {
                	$tailgate->setLSDs($input);
                } else {
                	$tailgate->deleteAllLSDs();
                }

                $tailgate->setSupervisors($input);
                $tailgate->setPermits($input);
                Self::editTailgateTasks($input, $tailgate);
                Self::editTailgateJobCompletionFields($input, $tailgate);

                $tailgate->save();
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
<?php

// Fix for incorrect hourly time
date_default_timezone_set('Canada/Mountain');

class WorkerManagementController extends AjaxController {
    
    public function manageWorkersView(){
        $loggedUser = Auth::user();
        $data['loggedUser'] = $loggedUser;
        $data['workers'] = Worker::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        $data['companies'] = Company::getFormatedForAdd();
        
        return View::make('webApp::worker-management.manage-workers',$data);
    }
    
    
    public function addWorkerAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        $input = Input::all();
        if (!$loggedUser->isAdmin() && $input['company_id'] != $loggedUser->company_id){
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        if (!Input::has('group_id')){
            return self::buildAjaxResponse(FALSE, 'Please fill in all the fields');
        }
        if (!$loggedUser->isAdmin() 
            && $loggedUser->company_id != Group::find( $input['group_id'])->businessUnit->division->company_id){
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        
        $validator = Validator::make($input, 
                array(
                    'auth_token' => 'required|min:10|max:16|unique:workers|alpha_num',
                    'first_name' => 'required',
                    'last_name'  => 'required'
                ) 
        );
        
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('auth_token','first_name','last_name'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $worker = new Worker();
        $worker->setGroupId($input['group_id']);
        $worker->auth_token = $input['auth_token'];
        $worker->api_key =  Worker::generateApiKey();
        $worker->first_name = $input['first_name'];
        $worker->last_name = $input['last_name'];
        $worker->save();
        
        if (filter_var($input['email'], FILTER_VALIDATE_EMAIL)){
            $data = array('email'=>$input['email'],
                          'token'=>$input['auth_token']);
            Mail::queue('webApp::emails.worker-add', $data, function($message) use ($data){
                $message->to($data['email'])->subject('White Knight Safety App Login!');
            }); 
        } 
        
        // Functionality removed
        // Queue::push('WorkerQuantumHandler', array('companyId' => $worker->company_id), 'wkss');

        return self::buildAjaxResponse(TRUE);
        
    }
    
    public function getWorkerDetailsAction($authToken){
        $worker = Worker::getByAuthToken($authToken);
        $worker->load('emergencyContacts');
        return self::buildAjaxResponse(TRUE, $worker);
    }
    
    public function editWorkerAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        
        $worker = Worker::getByAuthToken($input['auth_token']);
        $group = Group::find($input['group_id']);
        if ($group instanceof Group){
            if (!$loggedUser->isAdmin() 
                && $loggedUser->company_id != Group::find($input['group_id'])->businessUnit->division->company_id){
                die('You are not authorized to perform this action. Your IP address has been logged.');
            }
            if ($worker->group_id != $input['group_id']){
                $worker->setGroupId($input['group_id']);
            }
        }else{
            return self::buildAjaxResponse(false, 'Please fill in all the fields');
        }
        
        $worker->setFields($input);
        $worker->save();
        return self::buildAjaxResponse(TRUE, $worker);
        
    }
    
    
    public function disableWorkerAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        $authToken = str_replace('disable_', '', Input::get('disable_auth_token', null));
        
        $worker = Worker::getByAuthToken($authToken);
        
        if (!$loggedUser->isAdmin() && $loggedUser->company_id != $worker->company_id){
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        
        $worker->disabled = true;
        $worker->save();
        return self::buildAjaxResponse(TRUE);
        
    }
    
    public function deleteWorkerAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        $authToken = str_replace('delete_', '', Input::get('delete_auth_token', null));
        $worker = Worker::getByAuthToken($authToken);
        
        if (!$loggedUser->isAdmin() && $loggedUser->company_id != $worker->company_id){
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }

        Self::deleteWorkerTickets($worker->company_id, $worker->worker_id);
        
        $worker->delete(); // soft delete

        // Functionality removed
        // Queue::push('WorkerQuantumHandler', array('companyId' => $worker->company_id), 'wkss');
        
        return self::buildAjaxResponse(TRUE);    
    }
    
    // Performs hard delete of all worker tickets
    private function deleteWorkerTickets($companyId, $workerId){
        $tickets = Ticket::where('worker_id', '=', $workerId)
                         ->get()
                         ->all();
        if($tickets){
            foreach($tickets as $ticket){
                $ticketId = $ticket->ticket_id; // Used after ticket is deleted
                $workerId = $ticket->worker_id; // Used after ticket is deleted

                $ticket->review()->delete();
                $ticket->photos()->delete();
                $ticket->delete();

                // Files are store in Company > Tickets > Worker ID > Ticket ID
                $sm = new StorageManager;
                $sm->deleteTicketFolder($companyId, $workerId, $ticketId);
            }
        }
    }
    
    public function enableWorkerAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        $authToken = str_replace('enable_', '', Input::get('enable_auth_token', null));
        $worker = Worker::getByAuthToken($authToken);
        if (!$loggedUser->isAdmin() && $loggedUser->company_id != $worker->company_id){
            die('You are not authorized to perform this action. Your IP address has been logged.');
        }
        
        $worker->disabled = false;
        $worker->save();
        return self::buildAjaxResponse(TRUE);
        
    }
   
}
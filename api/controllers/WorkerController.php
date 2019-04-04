<?php



class WorkerController extends ApiController {
    
    
    /**
     * This handles the LOGIN call
     * 
     * @return JSONResponse
     */
    public function loginAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        
        if ($worker->profile_completed){
            return $this->createResponse(200,$worker->profile());
        }else{
            return $this->createResponse(203,$worker->getAutocompleteInfo());
        }
        
    }
    
    /**
     * This handles the REGISTER call
     * 
     * @return JSONResponse 
     */
    public function registerAction(){
        $input = Request::json()->all();
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        if ($worker->profile_completed){
            return $this->createResponse(403,$this->responses['tokenAlreadyRegistered']);
        }else{
            $worker->setFields($input);
            $worker->profile_completed = true;
            $worker->save();
            return $this->createResponse(201);
        }
        
    }
    
    public function getProfileAction(){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        return $this->createResponse(200,$worker->profile());
    }
    
    
    public function editProfileAction(){
        
        $input = Request::json()->all();
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $worker->setFields($input);
        if ($worker->save()){
            if (array_key_exists('emergencyContacts', $input)){
                $contacts = $input['emergencyContacts'];
                $worker->emergencyContacts()->delete();
                foreach ($contacts as $c){
                    $eC = EmergencyContact::firstOrNew(array('name'=>$c['name'],
                                                             'contact'=>$c['contact'],
                                                             'relationship'=>$c['relationship'],
                                                             'worker_id'=>$worker->worker_id
                                                        ));
                    $worker->emergencyContacts()->save($eC);
                }
            }
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
       
    }
    
    public function editSettingsAction(){
        $input = Request::json()->all();
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $worker->setSettings($input);
        if ($worker->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function saveProfilePhotoAction(){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        if (Input::hasFile('photo')){
            $profilePhoto = Input::file('photo');
            $this->storageManager->saveProfilePhoto($worker, $profilePhoto);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }
    
    public function getProfilePhotoAction(){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $photoPath = $this->storageManager->getProfilePhoto($worker);
        return $this->createImageResponse($photoPath);
    }
  
    public function saveSignatureAction(){
        $photo = Request::json()->get('photo');
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        if (Input::hasFile('photo')){
            $signaturePhoto = Input::file('photo');
            $signatureName = $this->storageManager->saveSignaturePhoto($worker, $signaturePhoto);

            return $this->createResponse(201,array('entity_id' => $signatureName));
        }
        return $this->createResponse(400);
    }
    
    public function getSignatureAction($signatureId){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $photoPath = $this->storageManager->getSignaturePhoto($worker, $signatureId);
        return $this->createImageResponse($photoPath);
    }
    
    public function deleteSignatureAction($signatureId){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $deleteResult = $this->storageManager->deleteSignaturePhoto($worker, $signatureId);
        if($deleteResult){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(404);
        }
    }
    
    
}
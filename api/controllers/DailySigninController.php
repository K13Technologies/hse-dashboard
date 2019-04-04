<?php



class DailySigninController extends ApiController {
    
    public function signinAction(){

        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        
        if (array_key_exists('api_key', $input)){
            $otherWorker =  Worker::getByApiKey(trim($input['api_key']));
        }else{
            $otherWorker = NULL;
        }
        if (empty($input['api_key']) OR 
                !$otherWorker instanceof Worker OR 
                $otherWorker->group_id != $worker->group_id){
            $workerId = NULL;
            $firstName = trim($input['first_name']);
            $lastName = trim($input['last_name']);
        }else{
            $workerId = $otherWorker->worker_id;
            $firstName = $otherWorker->first_name;
            $lastName  = $otherWorker->last_name;
        }
        $object = DailySignin::firstOrNew(array('ts'=>$timestamp,
                                                'created_at'=>$input['created_at'],
                                                'first_name'=>$firstName,
                                                'last_name'=>$lastName,
                                                'type'=>$input['type'],
                                                'worker_id'=>$workerId,
                                                'group_id'=>$worker->group_id,
                                                'company_id'=>$worker->company_id,
                                                ));
        if ($object->daily_signin_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->daily_signin_id));
        }else{
            $dailySign = $object;
            if($dailySign->save()){
                return $this->createResponse(201, array('entity_id' => (string)$dailySign->daily_signin_id));
            }
            return $this->createResponse(400);
        }
    }
        
        
    public function saveDailySigninSignatureAction($signinId){
        $dailySignin = DailySignin::find($signinId);
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        if($dailySignin->group_id != $worker->group_id){
            return $this->createResponse(403);
        }
        if (Input::hasFile('photo')){
            $photo = Input::file('photo');
            $this->storageManager->saveDailySigninSignature($dailySignin, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }

    public function getDailySigninSignatureAction($signinId){
        $dailySignin = DailySignin::find($signinId);
        $photo = $this->storageManager->getDailySigninSignature($dailySignin);
        return $this->createImageResponse($photo);
    }
    
    
}
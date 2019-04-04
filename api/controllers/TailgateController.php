<?php
class TailgateController extends ApiController {
    
    
   public function addTailgateAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();

        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Tailgate::firstOrNew(array('ts'=>$timestamp,
                                            'created_at'=>$input['created_at'],
                                            'worker_id'=>$worker->worker_id));
        if ($object->tailgate_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->tailgate_id));
        }else{
            $tailgate = $object;
            $tailgate->setFields($input);
            if ($tailgate->save()){
                $tailgate->setLocations($input);
                $tailgate->setSupervisors($input);
                $tailgate->setLSDs($input);
                $tailgate->setPermits($input);
                return $this->createResponse(201, array('entity_id' => (string)$tailgate->tailgate_id));
            }
            return $this->createResponse(400);
        }
    }
    
    public function getOneTailgateAction($tailgateId){
        return Tailgate::getWithFullDetails($tailgateId);
    }
    

    public function editTailgateAction($tailgateId){
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        $tailgate->setFields($input);
        
        if ($tailgate->save()){
            $tailgate->setLocations($input);
            $tailgate->setSupervisors($input);
            $tailgate->setLSDs($input);
            $tailgate->setPermits($input);
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    public function setTailgateHazardAssessmentAction($tailgateId){
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        $tailgate->setFields($input);
        
        if ($tailgate->save()){
            return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function setTailgateChecklistAction($tailgateId){
       
        if (!Request::json()->has('hazardChecklistItemIds')){
            return $this->createResponse(400,$this->responses['missingFlhaHazardItems']);
        }
        if (!is_array(Request::json()->get('hazardChecklistItemIds'))){
            return $this->createResponse(400,$this->responses['invalidFlhaHazardItemsType']);
        }
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        
        if ( $tailgate->setChecklist($input) ){
             return $this->createResponse(200);
        }
        return $this->createResponse(400,$this->responses['invalidFlhaHazardItems']);
    }
    
    
    public function setTailgateJobCompletionAction($tailgateId){
       
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        
        if (!$tailgate->completion instanceof JobCompletion){
            $completion = new JobCompletion();
            $completion->setFields($input);
            $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
            $completion->ts = $timestamp;
            $tailgate->completion()->save($completion);
            return $this->createResponse(200);
        }
        return $this->createResponse(403);
   
    }
    
    
    public function addTailgateTaskAction($tailgateId){
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        
        $object = TailgateTask::firstOrNew(array('title'=>$input['title'],
                                                 'tailgate_id'=>$tailgateId));
        if ($object->tailgate_task_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->tailgate_task_id));
        }else{
            $tailgateTask = $object;

            $tailgateTask->setFields($input);

            if ($tailgate->tasks()->save($tailgateTask)){
                return $this->createResponse(201, array('entity_id' => (string)$tailgateTask->tailgate_task_id));
            }
            return $this->createResponse(400);
        }
        
    }
    
    public function deleteTailgateTaskAction($tailgateTaskId){
        $tailgateTask = TailgateTask::getWithHazardListById($tailgateTaskId);
        
        if( $tailgateTask->delete()){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    public function getTailgateTaskAction($tailgateTaskId){
        $tailgateTask = TailgateTask::getWithHazardListById($tailgateTaskId);
        
        return $this->createResponse(200,$tailgateTask);
    }
    
  
    public function addTailgateTaskHazardAction($tailgateTaskId){
        $input = Request::json()->all();
        
        $tailgateTask = TailgateTask::find($tailgateTaskId);
        
        $object = TailgateTaskHazard::firstOrNew(array('description'=>$input['description'],
                                                       'tailgate_task_id'=>$tailgateTaskId));
        if ($object->tailgate_task_hazard_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->tailgate_task_hazard_id));
        }else{
            $tailgateTaskHazard =  $object;
            $tailgateTaskHazard->setFields($input);

            if ($tailgateTask->hazards()->save($tailgateTaskHazard)){
                return $this->createResponse(201, array('entity_id' => (string)$tailgateTaskHazard->tailgate_task_hazard_id));
            }
            return $this->createResponse(400);
        }
        
    }
    
    public function getTailgateTaskHazardAction($tailgateTaskHazardId){
        $tailgateTaskHazard = TailgateTaskHazard::find($tailgateTaskHazardId);
        return $this->createResponse(200, $tailgateTaskHazard);  
    }
      
    public function editTailgateTaskHazardAction($tailgateTaskHazardId){
        
        $input = Request::json()->all();
        $tailgateTaskHazard = TailgateTaskHazard::find($tailgateTaskHazardId);
        $tailgateTaskHazard->setFields($input);
        
        if ($tailgateTaskHazard->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    public function deleteTailgateTaskHazardAction($tailgateTaskHazardId){
        $tailgateTaskHazard = TailgateTaskHazard::find($tailgateTaskHazardId);
        
        if( $tailgateTaskHazard->delete()){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    
    
    public function addVisitorForSignoffAction($tailgateId){
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);

        $object = TailgateSignoffVisitor::firstOrNew(array('first_name'=>$input['first_name'],
                                                    'last_name'=>$input['last_name'],
                                                    'tailgate_id'=>$tailgateId));
        if ($object->signoff_visitor_id){
            return $this->createResponse(201,array('entity_id'=> (string)$object->signoff_visitor_id));
        }else{
            $visitor = new TailgateSignoffVisitor();
            $visitor->setFields($input);
            if ($tailgate->signoffVisitors()->save($visitor)){
                return $this->createResponse(201, array('entity_id' => (string)$visitor->signoff_visitor_id));
            }
            return $this->createResponse(400);
        }
    }
    
    
    public function getVisitorForSignoffAction($visitorId){
        
        $signoffVisitor = TailgateSignoffVisitor::find($visitorId);
        return $this->createResponse(200, $signoffVisitor);
        
    }
    
    
    public function editVisitorForSignoffAction($visitorId){
        
        $input = Request::json()->all();
        
        $signoffVisitor = TailgateSignoffVisitor::find($visitorId);
        $signoffVisitor->setFields($input);
        
        if ($signoffVisitor->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function deleteVisitorForSignoffAction($visitorId){
        $signoffVisitor = TailgateSignoffVisitor::find($visitorId);
        
        if( $signoffVisitor->delete()){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    
    
    public function addWorkerForSignoffAction($tailgateId){
        $input = Request::json()->all();
        
        $tailgate = Tailgate::find($tailgateId);
        
        $object = TailgateSignoffWorker::firstOrNew(array('first_name'=>$input['first_name'],
                                                        'last_name'=>$input['last_name'],
                                                        'tailgate_id'=>$tailgateId));
        if ($object->signoff_worker_id){
            return $this->createResponse(201,array('entity_id'=> (string)$object->signoff_worker_id));
        }else{
            $signoffWorker = new TailgateSignoffWorker();
            $signoffWorker->setFields($input);
            if ($tailgate->signoffWorkers()->save($signoffWorker)){
                return $this->createResponse(201, array('entity_id' => (string)$signoffWorker->signoff_worker_id));
            }
            return $this->createResponse(400);
        }
    }
    
    
    public function getWorkerForSignoffAction($workerId){
        
        $signoffWorker = TailgateSignoffWorker::find($workerId);
        return $this->createResponse(200, $signoffWorker);
        
    }
    
    
    public function editWorkerForSignoffAction($workerId){
        
        $input = Request::json()->all();
        
        $signoffWorker = TailgateSignoffWorker::find($workerId);
        $signoffWorker->setFields($input);
        
        if ($signoffWorker->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }

    
    public function deleteWorkerForSignoffAction($workerId){
        $signoffWorker = TailgateSignoffWorker::find($workerId);
        
        if( $signoffWorker->delete()){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    public function saveVisitorSignoffSignatureAction($visitorId){
        $visitor = TailgateSignoffVisitor::find($visitorId);
        if (Input::hasFile('photo')){
            $photo = Input::file('photo');
            $this->storageManager->saveTailgateSignoffVisitorSignature($visitor, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }
    
    public function getVisitorSignoffSignatureAction($visitorId){
        $visitor = TailgateSignoffVisitor::find($visitorId);
        $photo = $this->storageManager->getTailgateSignoffVisitorSignature($visitor);
        return $this->createImageResponse($photo);
    }
    
    public function saveWorkerSignoffSignatureAction($workerId){
        $worker = TailgateSignoffWorker::find($workerId);
        if (Input::hasFile('photo')){
            $photo = Input::file('photo');
            $this->storageManager->saveTailgateSignoffWorkerSignature($worker, $photo);
            return $this->createResponse(201);
        }
        return $this->createResponse(400);
    }
    
    public function getWorkerSignoffSignatureAction($workerId){
        $worker = TailgateSignoffWorker::find($workerId);
        $photo = $this->storageManager->getTailgateSignoffWorkerSignature($worker);
        return $this->createImageResponse($photo);
    }
    
}
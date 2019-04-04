<?php
class PositiveObservationController extends ApiController {
    
    
   public function addPositiveObservationAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = PositiveObservation::firstOrNew(array('ts'=>$timestamp,
                                                        'created_at'=>$input['created_at'],
                                                        'worker_id'=>$worker->worker_id));
        if ($object->positive_observation_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$object->positive_observation_id));
        }else{
            $input['positive_observation_activity_id'] = Request::json()->get('positive_observation_activity_id', NULL);
            $input['categoryIds'] = Request::json()->get('categoryIds', array());
            $input['personsObserved'] = Request::json()->get('personsObserved', array());
            $po = $object;
            $po->setFields($input);
            //save first, so we get the id

            if ($po->save()){
                $po->setCategories($input);
                $po->setPersonsObserved($input);
                return $this->createResponse(201, array('entity_id' => (string)$po->positive_observation_id));
            }
            return $this->createResponse(400);
        }
    }
    
    /**
     * This handles the get Positive Observation list call
     * 
     * @return JSONResponse 
     */
    public function getPositiveObservationListAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $poList = PositiveObservation::getRecentPOForWorker($worker->worker_id);
        return $this->createResponse(200,$poList);
        
    }
    
    
    public function editPositiveObservationAction($positiveObservationId){
        
        $input = Request::json()->all();
        
        $input['positive_observation_activity_id'] = Request::json()->get('positive_observation_activity_id', NULL);
        $input['categoryIds'] = Request::json()->get('categoryIds', array());
        $input['personsObserved'] = Request::json()->get('personsObserved', array());
        
        $po = PositiveObservation::find($positiveObservationId);
        
        $error = DB::transaction(function() use ($po, $input)
        {
            $po->setFields($input);
            $po->setCategories($input);
            $po->setPersonsObserved($input);
            $po->save();
        });
        
        if ($error == NULL ){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function deletePositiveObservationAction($positiveObservationId){
        $positiveObservation = PositiveObservation::find($positiveObservationId);
        $error = DB::transaction(function() use ($positiveObservation)
        {
            $positiveObservation->photos->each(function($photo){
                $photo->delete();
            });
            $positiveObservation->delete();
        });
        if($error == NULL){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    
    
    
    public function savePositiveObservationPhotoAction($positiveObservationId){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $positiveObservation = PositiveObservation::find($positiveObservationId);
        
        if (Input::hasFile('photo')){
            $positiveObservationPhoto = Input::file('photo');
            $photo = Photo::firstOrNew(array('original_name'=>$positiveObservationPhoto->getClientOriginalName(),
                                             'imageable_type'=>'PositiveObservation',
                                             'imageable_id'=>$positiveObservation->positive_observation_id,
                                             'worker_id'=>$worker->worker_id));
            
            if ($photo->photo_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$photo->name));
            }else{
                $positiveObservationPhotoDetails = $this->storageManager->savePositiveObservationPhoto($positiveObservation, $positiveObservationPhoto);

                $photo = new Photo();
                $photo->path = $positiveObservationPhotoDetails['path'];
                $photo->name = $positiveObservationPhotoDetails['name'];
                $photo->original_name = $positiveObservationPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $positiveObservation->photos()->save($photo);

                return $this->createResponse(201,array('entity_id' => $positiveObservationPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }
    
    public function getPositiveObservationPhotoAction($positiveObservationPhotoId){
        $photo = Photo::getPhotoByPhotoName($positiveObservationPhotoId);
        return $this->createImageResponse($photo->path);
    }
    
    public function deletePositiveObservationPhotoAction($positiveObservationPhotoId){
        $photo = Photo::getPhotoByPhotoName($positiveObservationPhotoId);
        
        if ($photo->delete()){
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
}
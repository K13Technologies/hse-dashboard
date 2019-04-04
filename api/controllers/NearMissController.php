<?php



class NearMissController extends ApiController {
    
    
   public function addNearMissAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        
        $object = NearMiss::firstOrNew(array('ts'=>$timestamp,
                                             'created_at'=>$input['created_at'],
                                             'worker_id'=>$worker->worker_id));
        if ($object->near_miss_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->near_miss_id));
        }else{
            $nearMiss = $object;
            $input['hazard_category_ids'] = Request::json()->get('hazard_category_ids', array());
            $input['hazard_activity_id'] = Request::json()->get('hazard_activity_id', NULL);
            $nearMiss->setFields($input);
            if ($nearMiss->save()){
                $nearMiss->setHazardCategories($input);
                return $this->createResponse(201, array('entity_id' => (string)$nearMiss->near_miss_id));
            }
            return $this->createResponse(400);
        }        
    }
    
    /**
     * This handles the get Vehicle list call
     * 
     * @return JSONResponse 
     */
    public function getNearMissListAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $nearMissesList = NearMiss::getRecentNearMissesForWorker($worker->worker_id);
        return $this->createResponse(200,$nearMissesList);
        
    }
    
    
    public function editNearMissAction($nearMissId){
        
        $input = Request::json()->all();
        
        $input['hazard_activity_id'] = Request::json()->get('hazard_activity_id', NULL);
        $input['hazard_category_ids'] = Request::json()->get('hazard_category_ids', array());
        
        
        $nearMiss = NearMiss::find($nearMissId);
        
        $error = DB::transaction(function() use ($nearMiss, $input)
        {
            $nearMiss->setFields($input);
            $nearMiss->setHazardCategories($input);
            $nearMiss->save();
        });
        
        if ($error == NULL ){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function deleteNearMissAction($nearMissId){
        $nearMiss = NearMiss::find($nearMissId);
        $error = DB::transaction(function() use ($nearMiss)
        {
            $nearMiss->hazardCategories()->sync(array());
            $nearMiss->photos->each(function($photo){
                $photo->delete();
            });
            $nearMiss->delete();
        });
        if($error == NULL){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    
    
    
    public function saveNearMissPhotoAction($nearMissId){

        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $nearMiss = NearMiss::find($nearMissId);
        
        if (Input::hasFile('photo')){
            $nearMissPhoto = Input::file('photo');
            $photo = Photo::firstOrNew(array('original_name'=>$nearMissPhoto->getClientOriginalName(),
                                             'imageable_type'=>'NearMiss',
                                             'imageable_id'=>$nearMiss->near_miss_id,
                                             'worker_id'=>$worker->worker_id));
            if ($photo->photo_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$photo->name));
            }else{
                
                $nearMissPhotoDetails = $this->storageManager->saveNearMissPhoto($nearMiss, $nearMissPhoto);

                $photo = new Photo();
                $photo->path = $nearMissPhotoDetails['path'];
                $photo->name = $nearMissPhotoDetails['name'];
                $photo->original_name = $nearMissPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $nearMiss->photos()->save($photo);

                return $this->createResponse(201,array('entity_id' => $nearMissPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
        
        
    }
    
    public function getNearMissPhotoAction($nearMissPhotoId){
        $photo = Photo::getPhotoByPhotoName($nearMissPhotoId);
        return $this->createImageResponse($photo->path);
    }
    
    public function deleteNearMissPhotoAction($nearMissPhotoId){
        $photo = Photo::getPhotoByPhotoName($nearMissPhotoId);
        
        if ($photo->delete()){
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
}
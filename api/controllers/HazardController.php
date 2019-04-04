<?php



class HazardController extends ApiController {
   
    
   public function addHazardAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Hazard::firstOrNew(array('ts'=>$timestamp,
                                           'created_at'=>$input['created_at'],
                                           'worker_id'=>$worker->worker_id));
        if ($object->hazard_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->hazard_id));
        }else{
            $hazard = $object;
            $input['hazard_activity_id'] = Request::json()->get('hazard_activity_id', NULL);
            $input['hazard_category_ids'] = Request::json()->get('hazard_category_ids', array());
            $hazard->setFields($input);
            if($hazard->save()){
                $hazard->setHazardCategories($input);
                return $this->createResponse(201, array('entity_id' => (string)$hazard->hazard_id));
            }
            return $this->createResponse(400);
        }
    }
    
    /**
     * This handles the get Vehicle list call
     * 
     * @return JSONResponse 
     */
    public function getHazardListAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $hazardList = Hazard::getRecentHazardsForWorker($worker->worker_id);
        return $this->createResponse(200,$hazardList);
        
    }
    
    
    public function editHazardAction($hazardId){
        
        $input = Request::json()->all();
        
        $input['hazard_activity_id'] = Request::json()->get('hazard_activity_id', NULL);
        $input['hazard_category_ids'] = Request::json()->get('hazard_category_ids', array());
        
        $hazard = Hazard::find($hazardId);
        
        $error = DB::transaction(function() use ($hazard, $input)
        {
            $hazard->setFields($input);
            $hazard->setHazardCategories($input);
            $hazard->save();
        });
        
        if ($error == NULL ){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    public function deleteHazardAction($hazardId){
        $hazard = Hazard::find($hazardId);
        $error = DB::transaction(function() use ($hazard)
        {
            $hazard->hazardCategories()->sync(array());
            $hazard->photos->each(function($photo){
                $photo->delete();
            });
            $hazard->delete();
        });
        if($error == NULL){
            return $this->createResponse(204);
        }else{
            return $this->createResponse(304);
        }
    }
    
    
    
    
    public function saveHazardPhotoAction($hazardId){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $hazard = Hazard::find($hazardId);
        
        if (Input::hasFile('photo')){
            $hazardPhoto = Input::file('photo');
            
            $photo = Photo::firstOrNew(array('original_name'=>$hazardPhoto->getClientOriginalName(),
                                             'imageable_type'=>'Hazard',
                                             'imageable_id'=>$hazard->hazard_id,
                                             'worker_id'=>$worker->worker_id));
            
            if ($photo->photo_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$photo->name));
            }else{
                $hazardPhotoDetails = $this->storageManager->saveHazardPhoto($hazard, $hazardPhoto);

                $photo = new Photo();
                $photo->path = $hazardPhotoDetails['path'];
                $photo->name = $hazardPhotoDetails['name'];
                $photo->original_name = $hazardPhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $hazard->photos()->save($photo);

                return $this->createResponse(201,array('entity_id' => $hazardPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }
    
    public function getHazardPhotoAction($hazardPhotoId){
        $photo = Photo::getPhotoByPhotoName($hazardPhotoId);
        return $this->createImageResponse($photo->path);
    }
    
    public function deleteHazardPhotoAction($hazardPhotoId){
        $photo = Photo::getPhotoByPhotoName($hazardPhotoId);
        
        if ($photo->delete()){
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
}
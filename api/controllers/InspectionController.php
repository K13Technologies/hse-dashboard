<?php



class InspectionController extends ApiController {
    
    public function addInspectionAction($vehicleId){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));

        $input = Request::json()->all();
        $timestamp = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $object = Inspection::firstOrNew(array('ts'=>$timestamp,
                                               'created_at'=>$input['created_at'],
                                               'vehicle_id'=>$vehicleId,
                                               'worker_id'=>$worker->worker_id));
        if ($object->inspection_id){
             return $this->createResponse(201,array('entity_id'=> (string)$object->inspection_id));
        }else{
            $inspection = $object;
            $inspection->setFields($input);
            if($inspection->push()){
                $inspection->buildActions($input);
                return $this->createResponse(201,array('entity_id'=>(string)$inspection->inspection_id));
            }
            return $this->createResponse(400);
        }
        
    }
    
    public function getInspectionAction($inspectionId){
        $inspection = Inspection::getWithDetails($inspectionId);
        return $this->createResponse(200,$inspection);
    }
    
    public function editInspectionAction($inspectionId){
        $inspection = Inspection::find($inspectionId);
        $input = Request::json()->all();
        $inspection->setFields($input);
        
        if ($inspection->push()){
             $inspection->actions()->delete();
             $inspection->buildActions($input);
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    public function deleteInspectionAction($inspectionId){
        $inspection = Inspection::find($inspectionId);
        if ($inspection->delete()){
             return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
    public function saveInspectionPhotoAction($inspectionId){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $inspection = Inspection::find($inspectionId);
        if (Input::hasFile('photo')){
            $inspectionPhoto = Input::file('photo');
            $part = Input::get('part_name','inspection');
            $photo = Photo::firstOrNew(array('original_name'=>$inspectionPhoto->getClientOriginalName(),
                                             'imageable_type'=>'Inspection',
                                             'imageable_id'=>$inspection->inspection_id,
                                             'worker_id'=>$worker->worker_id,
                                             'used_for' =>$part));
            if ($photo->photo_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$photo->name));
            }else{
                $inspectionPhotoDetails = $this->storageManager->saveInspectionPhoto($inspection, $inspectionPhoto);
                $photo->path = $inspectionPhotoDetails['path'];
                $photo->name = $inspectionPhotoDetails['name'];
                $inspection->photos()->save($photo);

                return $this->createResponse(201,array('entity_id' => $inspectionPhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
    }
    
    public function getInspectionPhotoAction($inspectionPhotoId){
        $photo = Photo::getPhotoByPhotoName($inspectionPhotoId);
        return $this->createImageResponse($photo->path);
    }
    
    public function deleteInspectionPhotoAction($inspectionPhotoId){
        $photo = Photo::getPhotoByPhotoName($inspectionPhotoId);
        
        if ($photo->delete()){
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
}
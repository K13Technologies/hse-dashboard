<?php



class VehicleController extends ApiController {
    
    
   public function addVehicleAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        if ( array_key_exists('license_plate', $input)){
            $vehicle = Vehicle::firstOrNew(array('license_plate'=>$input['license_plate'],
                                                 'company_id'=>$worker->company_id));
            
            if ($vehicle->vehicle_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$vehicle->vehicle_id));
            }
        }

        $vehicle = new Vehicle();
        $vehicle->setFields($input, $worker);
        
        if ($vehicle->save()){
             return $this->createResponse(201,array('entity_id'=> (string)$vehicle->vehicle_id));
        }
        return $this->createResponse(400);
    }
    
    /**
     * This handles the get Vehicle list call
     * 
     * @return JSONResponse 
     */
    public function getVehicleListAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        
        // How it was done originally, which works awesomely, but I don't know how to make it only for non deleted vehicles
        //$vehicleList = $worker->company->vehicles;

        // This would be another way of doing it in which we could have more control over the actual query to the DB
        // Later we can customize it for projects and business units
        $vehicleList = Vehicle::getVehiclesForCompany($worker["company_id"]);
            
        return $this->createResponse(200, $vehicleList);
    }
    
    
    public function editVehicleAction($vehicleId){
        
        $input = Request::json()->all();
        $vehicle = Vehicle::find($vehicleId);
        
        $licensePlateDuplicateCheck = Vehicle::getByLicensePlate($input['license_plate']);
        
        if ($licensePlateDuplicateCheck instanceof Vehicle && 
            ($licensePlateDuplicateCheck->vehicle_id != $vehicle->vehicle_id)){
              return $this->createResponse(400,$this->responses['licensePlateAlreadyExists']);
        }
        
        $vehicle->setFields($input);
        
        if ($vehicle->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    
    
    public function getVehicleAction($vehicleId){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $vehicle = Vehicle::getVehicleWithRecentInspections($vehicleId, $worker->worker_id);
        return $this->createResponse(200,$vehicle->toArray());
    }
    
    public function saveVehiclePhotoAction($vehicleId){
        $photo = Request::json()->get('photo');
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $vehicle = Vehicle::find($vehicleId);
        
        if (Input::hasFile('photo')){
            $vehiclePhoto = Input::file('photo');
            
            $photo = Photo::firstOrNew(array('original_name'=>$vehiclePhoto->getClientOriginalName(),
                                             'imageable_type'=>'Vehicle',
                                             'imageable_id'=>$vehicle->vehicle_id,
                                             'worker_id'=>$worker->worker_id));
            
            if ($photo->photo_id){
                 return $this->createResponse(201,array('entity_id'=> (string)$photo->name));
            }else{
                $vehiclePhotoDetails = $this->storageManager->saveVehiclePhoto($vehicle, $vehiclePhoto);

                $photo = new Photo();
                $photo->path = $vehiclePhotoDetails['path'];
                $photo->name = $vehiclePhotoDetails['name'];
                $photo->original_name = $vehiclePhoto->getClientOriginalName();
                $photo->worker_id = $worker->worker_id;
                $vehicle->photos()->save($photo);
                return $this->createResponse(201,array('entity_id' => $vehiclePhotoDetails['name']));
            }
        }
        return $this->createResponse(400);
        
    }
    
    public function getVehiclePhotoAction($vehiclePhotoId){
        $photo = Photo::getPhotoByPhotoName($vehiclePhotoId);
        return $this->createImageResponse($photo->path);
    }
    
    public function deleteVehiclePhotoAction($vehiclePhotoId){
        $photo = Photo::getPhotoByPhotoName($vehiclePhotoId);
        
        if ($photo->delete()){
            $this->storageManager->deletePhoto($photo);
            return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
}
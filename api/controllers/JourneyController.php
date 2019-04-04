<?php



class JourneyController extends ApiController {
    
    
   public function addLocationAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $location = new JourneyLocation();
        $location->setFields($input, $worker);
        
        if ($location->save()){
             return $this->createResponse(201,array('entity_id'=> (string)$location->location_id));
        }
        return $this->createResponse(400);
    }
    
    /**
     * This handles the get Vehicle list call
     * 
     * @return JSONResponse 
     */
    public function getLocationListAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
       
        $locationList = JourneyLocation::getAllForWorker($worker);
        return $this->createResponse(200,$locationList);
        
    }
    
    
    public function editLocationAction($locationId){
        
        $input = Request::json()->all();
        $location = JourneyLocation::find($locationId);
        
        $location->setFields($input);
        
        if ($location->save()){
             return $this->createResponse(200);
        }
        return $this->createResponse(304);
    }
    
    public function deleteLocationAction($locationId){
        
        $location = JourneyLocation::find($locationId);
        
        if ($location->delete()){
             return $this->createResponse(204);
        }
        return $this->createResponse(304);
    }
    
    
    public function startJourneyAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $now = date('Y-m-d H:i:s');
        if (!isset($input['started_at'])){
            $input['created_at'] = $now;
        }else{
            $input['created_at'] = $input['started_at'];
        }
        $input['started_at'] = $now;
        
        $timestampStart = WKSSDate::getTsFromDateWithTz($input['started_at']);
        $timestampCreate = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $timestampNow = WKSSDate::getTsFromDateWithTz($now);
        
        $hasUncompleted = Journey::uncompletedForWorker($worker);
        if ($hasUncompleted){
             if(date($hasUncompleted->created_at) == date($input['created_at'])){
                 return $this->createResponse(201);
             }
             $hasUncompleted->finished_at = $now;
             $hasUncompleted->ts_finished = $timestampNow;
             $hasUncompleted->save();
        }
        
        $journey = new Journey();
        $journey->setFields($input, $worker);
        $journey->ts_started = $timestampStart; 
        $journey->ts_created = $timestampCreate; 
        
        if ($journey->save()){
             return $this->createResponse(201);
        }
        return $this->createResponse(400);
        
    }
    
    
    public function checkinForJourneyAction(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $journey = Journey::uncompletedForWorker($worker);
        if (!$journey instanceof Journey){
             return $this->createResponse(403,$this->responses['noJourneyInProgress']);
        }
        $now = date('Y-m-d H:i:s');
        $input['created_at'] = $now;
        $input['ts'] = WKSSDate::getTsFromDateWithTz($now);
        
        $error = DB::transaction(function() use ($journey, $input)
        {
            $checkin = new JourneyCheckin();
            $checkin->setFields($input);
            if (isset($input['journey_finished']) && $input['journey_finished']){
                $journey->finished_at = $checkin->created_at;
                $journey->ts_finished = $checkin->ts;
                $journey->save();
            }
            $journey->checkins()->save($checkin);
        });
        if ($error == NULL){
             return $this->createResponse(201);
        }
        return $this->createResponse(400);
        
    }
    
    public function helpLinesAction(){
        $worker = Worker::getByAuthToken(Input::get('authToken'));
	$phoneNumbers = array();
	$radioStations = array();
	foreach ($worker->company->phoneNumbers()->get() as $p){
		$phoneNumbers[] = $p->value;
	}
	foreach ($worker->company->radioStations()->get() as $r){
		$radioStations[] = $r->value;
	}
  	$response = array('phoneNumbers'=>$phoneNumbers,
                          'radioStations'=>$radioStations,
                          'namedPhoneNumbers'=>$worker->company->phoneNumbers()->get()->toArray(),
                          'namedRadioStations'=>$worker->company->radioStations()->get()->toArray()
                         );
        
        return $this->createResponse(200,$response);
    }
    
    
    public function startJourneyV2Action(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $now = date('Y-m-d H:i:s');
        if (!isset($input['started_at'])){
            $input['created_at'] = $now;
        }else{
            $input['created_at'] = $input['started_at'];
        }
        $input['started_at'] = $now;
        
        $timestampStart = WKSSDate::getTsFromDateWithTz($input['started_at']);
        $timestampCreate = WKSSDate::getTsFromDateWithTz($input['created_at']);
        $timestampNow = WKSSDate::getTsFromDateWithTz($now);
        
        $hasUncompleted = JourneyV2::uncompletedForWorker($worker);
        if ($hasUncompleted){
             $hasUncompleted->finished_at = $now;
             $hasUncompleted->ts_finished = $timestampNow;
             $hasUncompleted->save();
        }
        
        $journey = new JourneyV2();
        $journey->setFields($input, $worker);
        $journey->ts_started = $timestampStart; 
        $journey->ts_created = $timestampCreate; 
        
        if ($journey->save()){
            $journey->setEndpoints($input);
             return $this->createResponse(201);
        }
        return $this->createResponse(400);
        
    }
    
    public function changeJourneyEndpointsV2Action(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $hasUncompleted = JourneyV2::uncompletedForWorker($worker);
        if ($hasUncompleted){
            $hasUncompleted->setEndpoints($input);
            return $this->createResponse(200);
        }
        return $this->createResponse(401);
        
    }
    
    
    public function checkinForJourneyV2Action(){
        
        $worker = Worker::getByAuthToken(Input::get('authToken'));
        $input = Request::json()->all();
        
        $journey = JourneyV2::uncompletedForWorker($worker);
        if (!$journey instanceof JourneyV2){
             return $this->createResponse(403,$this->responses['noJourneyInProgress']);
        }
        $now = date('Y-m-d H:i:s');
        $input['created_at'] = $now;
        $input['ts'] = WKSSDate::getTsFromDateWithTz($now);
        
        $error = DB::transaction(function() use ($journey, $input)
        {
            $checkin = new JourneyCheckinV2();
            $checkin->setFields($input);
            if (isset($input['journey_finished']) && $input['journey_finished']){
                $journey->finished_at = $checkin->created_at;
                $journey->ts_finished = $checkin->ts;
                $journey->save();
            }
            if (isset($input['journey_arrived']) && $input['journey_arrived']){
                $lastLocations = $journey->separateEndpoints();
                $lastLocation = $lastLocations[1];
                if (count($lastLocation)){
                    $lastLocation = $lastLocation[0];
                    $lastLocation->arrived = $input['created_at'];
                    $lastLocation->ts_arrived = $input['ts'];
                    $lastLocation->save();
                }
            }
            $journey->checkins()->save($checkin);
        });
        if ($error == NULL){
             return $this->createResponse(201);
        }
        return $this->createResponse(400);
        
    }
    
}
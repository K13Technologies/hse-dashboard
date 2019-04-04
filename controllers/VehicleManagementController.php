<?php

class VehicleManagementController extends AjaxController {
    
    public function manageVehiclesView(){
        $loggedUser = Auth::user();
        $data['vehicles'] = Vehicle::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        
        return View::make('webApp::vehicle-management.manage-vehicles',$data);
    }
    
    public function getVehicleDetailsAction($authToken){
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $vehicle = Vehicle::find($authToken);
        return self::buildAjaxResponse(TRUE, $vehicle);
    }
    
    public function editVehicleAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $input = array();
        foreach (Input::all() as $key=>$val){
            $key = str_replace('edit_', '', $key);
            $input[$key] = $val;
        }
        $vehicleId = (int)$input['vehicle_id'];
        $validator = Validator::make($input, 
            array(
                'license_plate' => "required|unique:vehicles,license_plate,$vehicleId,vehicle_id"
            ) 
        );
        if ($validator->fails()){
            $formatted = self::validatorFormat($validator->messages(), array('license_plate'));
            return self::buildAjaxResponse(FALSE, $formatted);
        }
        
        $vehicle = Vehicle::find($input['vehicle_id']);
        
        if (!$loggedUser->isAdmin() && 
            !in_array($vehicle->group_id, Group::getAllForCompany($loggedUser->company_id))){
            die('hack');
        }
        
        $vehicle->setFields($input);
        $vehicle->save();
        return self::buildAjaxResponse(TRUE, $vehicle);
        
    }    
    
    public function vehicleDetailsView($vehicleId, $inspectionId = NULL){
        $vehicle = Vehicle::with('inspections')->find($vehicleId);
        if (!Auth::user()->isAdmin() && $vehicle->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $inspection = Inspection::find($inspectionId);
        if ($inspectionId!=NULL && (!$inspection instanceOf Inspection || $inspection->vehicle_id != $vehicleId)){
            return Redirect::to('/');
        }
        if(count($vehicle->inspections) && $inspectionId==NULL){
            return Redirect::to("vehicle-management/view/{$vehicleId}/{$vehicle->inspections()->first()->inspection_id}");
        }
        
        if($inspection instanceof Inspection){
            $inspection->load('actions');
            $actions = array();
            $componentPhotos = array();
            
            foreach ($inspection->actions as $action){
                $actions[$action->action_type] = $action;
            }
            $inspection->actions = $actions;
            foreach ($inspection->photos as $ph){
                    $part = $ph->used_for;
                    if ($part != "" && !empty($inspection->$part)){
                        $componentPhotos[$part][] = $ph;
                    }
            }
            $inspection->componentPhotos = $componentPhotos;
        }
//        dd($inspection);
        $data['vehicle']= $vehicle;
        $data['vehicleInspections'] = $vehicle->inspections()->orderBy('ts','ascending')->get();
        $data['inspection']= $inspection;
        return View::make('webApp::vehicle-management.view-vehicle',$data);
    }
    
    
    public function vehicleDetailsExport($vehicleId, $inspectionId = NULL){
        $vehicle = Vehicle::with('inspections')->find($vehicleId);
        if (!Auth::user()->isAdmin() && $vehicle->addedBy->company_id != Auth::user()->company_id){
            return Redirect::to('/');
        }
        $inspection = Inspection::find($inspectionId);
        if ($inspectionId!=NULL && (!$inspection instanceOf Inspection || $inspection->vehicle_id != $vehicleId)){
            return Redirect::to('/');
        }
        if(count($vehicle->inspections) && $inspectionId==NULL){
            return Redirect::to("vehicle-management/view/{$vehicleId}/{$vehicle->inspections()->first()->inspection_id}");
        }
        
        $inspection->load('actions');
        $actions = array();
        $componentPhotos = array();

        foreach ($inspection->actions as $action){
            $actions[$action->action_type] = $action;
        }
        $inspection->actions = $actions;
        foreach ($inspection->photos as $ph){
            $part = $ph->used_for;
            if ($part != "" && !empty($inspection->$part)){
                $componentPhotos[$part][] = $ph;
            }
        }
        $inspection->componentPhotos = $componentPhotos;
        
        $data['vehicle']= $vehicle;
        $data['inspection']= $inspection;

        $html = View::make('webApp::vehicle-management.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        
        $filename = $vehicle->license_plate." ".$inspection->title." ".$inspection->created_at.".pdf";
        return $pdf->download($filename);
    }
    
    
    public function vehicleDetailsMail(){
        $vehicleId = Input::get('vehicle_id');
        $inspectionId = Input::get('inspection_id');
        $email = Input::get('email');
        
        $vehicle = Vehicle::with('inspections')->find($vehicleId);
        if (!Auth::user()->isAdmin() && $vehicle->addedBy->company_id != Auth::user()->company_id){
            die('hack');
        }
        $inspection = Inspection::find($inspectionId);
        if ($inspectionId!=NULL && (!$inspection instanceOf Inspection || $inspection->vehicle_id != $vehicleId)){
            die('hack');
        }
        $inspection->load('actions');
        $actions = array();
        foreach ($inspection->actions as $action){
            $actions[$action->action_type]=$action;
        }
        $inspection->actions = $actions;
        $data['vehicle']= $vehicle;
        $data['inspection']= $inspection;
        
        $html = View::make('webApp::vehicle-management.export2',$data)->render();
        
        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = StorageManager::saveTempInspection($pdf,$vehicle,$inspection);
        
        $data = array('email'=>$email,
                      'type' =>'Vehicle Inspection Card',
                      'object'=>$inspection,
                      'attach'=>$filename);
        return self::sendAttachmentEmail($data);
    }
    
    public function getVehicleInspectionDetailsAction($inspectionId){
        if (!Request::ajax()){
            die('hack');
        }
        
        $inspection = Inspection::getWithDetails($inspectionId);
        
        $loggedUser = Auth::user();
        if (!$loggedUser->isAdmin() && 
            !in_array($inspection->addedBy->group_id, Group::getAllForCompany($loggedUser->company_id))){
            die('hack');
        }
        
        return self::buildAjaxResponse(TRUE, $inspection);
    }
    
    
    public function addInspectionAction(){
        $actionId = Input::get('inspection_action_id');
        $completedOn = Input::get('completed_on','');
        $actionDescription = Input::get('action','');
        $action = InspectionAction::find($actionId);
        
        if ($completedOn == ''){
            $action->action = trim($actionDescription);
            $action->completed_on = NULL;
        }else{
            if (date(strtotime($completedOn))){
              $action->completed_on = $completedOn;
              $action->inspection->vehicle->last_action_date = $completedOn;
            }
        }
        $action->save();
        if ($action->inspection->unresolvedActionsCount() == 0){
            $action->inspection->action_required=false;
        }else{
            if (!$action->inspection->action_required) {
                $action->inspection->action_required=true;
            }
        }
        $action->push();
        return self::buildAjaxResponse(TRUE, TRUE);
//        dd($action);
    }

    public function deleteVehicleFullyAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('hack');
        }
        $vehicleId = str_replace('delete_', '', Input::get('delete_vehicle_id', null));
        
        $vehicle = Vehicle::find($vehicleId);

        if (!$loggedUser->isAdmin() && 
            !in_array($vehicle->group_id, Group::getAllForCompany($loggedUser->company_id))){
            die('hack');
        }
        
        //$vehicle->delete();
        return self::buildAjaxResponse(TRUE);
        
    }

    public function deleteVehicleAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax())
        {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $vehicleID = str_replace('delete_', '', Input::get('delete_vehicle_id', null));
        $vehicle = Vehicle::getByID($vehicleID);      

        $vehicleCompanyID = Vehicle::getCompanyID($vehicle->vehicle_id);
        // Pull the value out of the JSON
        $vehicleCompanyID = $vehicleCompanyID['company_id']; 

        if($loggedUser->company_id == $vehicleCompanyID || $loggedUser->isAdmin()) {
            $vehicle->deleted_at = date('Y-m-d H:i:s');
            $vehicle->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }
}
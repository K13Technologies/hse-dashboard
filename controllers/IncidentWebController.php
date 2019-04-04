<?php

class IncidentWebController extends AjaxController {

    public function IncidentCardsView() {
        $loggedUser = Auth::user();
        $data['incidents'] = Incident::getForCompany($loggedUser);
        $data['user'] = $loggedUser;
        return View::make('webApp::incident-cards.list', $data);
    }

    public function IncidentCardDetailsView($incidentId) {
        $incident = Incident::getWithFullDetails($incidentId);
        if (!Auth::user()->isAdmin() && $incident->addedBy->company_id != Auth::user()->company_id) {
            return Redirect::to('/');
        }
        $data['incident'] = $incident;
        $data['activities'] = Activity::all();
        $data['incidentTypes'] = IncidentType::all();
        return View::make('webApp::incident-cards.view', $data);
    }

    public function IncidentCardDetailsExport($hazardId) {
        $incident = Incident::find($hazardId);
        if (!Auth::user()->isAdmin() && $incident->addedBy->company_id != Auth::user()->company_id) {
            return Redirect::to('/');
        }
        $data['incident'] = $incident;
//        return View::make('webApp::hazard-cards.export',$data);
        $html = View::make('webApp::incident-cards.export2', $data)->render();

        $pdf = PDF::loadHTML($html)->setOrientation('landscape');
        $filename = $incident->title . " " . $incident->created_at . '.pdf';
        return $pdf->download($filename);
    }

    public function incidentCardDetailsMail() {
        $incidentId = Input::get('incident_id');
        $email = Input::get('email');

        $incident = Incident::find($incidentId);
        if (!Auth::user()->isAdmin() && $incident->addedBy->company_id != Auth::user()->company_id) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }
        $data['incident'] = $incident;
//        return View::make('webApp::hazard-cards.export',$data);
        $html = View::make('webApp::incident-cards.export2', $data)->render();

        $pdf = PDF::loadHTML($html)->setOrientation('landscape');

        $filename = StorageManager::saveTempIncident($pdf, $incident);

        $data = array('email' => $email,
            'object' => $incident,
            'type' => 'Incident Card',
            'attach' => $filename);
        return self::sendAttachmentEmail($data);
    }

    public function addAction() {
        $incidentId = Input::get('incident_id');
        $completedOn = Input::get('completed_on', '');
        $actionDescription = Input::get('action', '');
        $incident = Incident::find($incidentId);

        if ($completedOn == '') {
            $incident->action = $actionDescription;
            $incident->completed_on = NULL;
        } else {
            if (date(strtotime($completedOn))) {
                $incident->completed_on = $completedOn;
            }
        }
        $incident->save();
        return self::buildAjaxResponse(TRUE, TRUE);
    }

    public function getPersonDetailsAction($personId) {
        $person = IncidentPerson::find($personId);
        if (!Auth::user()->isAdmin() && $person->incident->addedBy->company_id != Auth::user()->company_id) {
            return self::buildAjaxResponse(false);
        }
        $person->strType = IncidentPerson::$types[$person->type];
        $person->strEmploymentStatus = IncidentPerson::$employeeStatuses[$person->employment_status];
        if ($person->ts_on_shift) {
            $person->shift_time = WKSSDate::display($person->ts_on_shift, $person->time_on_shift);
        }
        if ($person->ts_of_incident) {
            $person->incident_time = WKSSDate::display($person->ts_of_incident, $person->time_of_incident);
        }
        unset($person->incident);
        return self::buildAjaxResponse(TRUE, $person);
    }

    public function incidentDeleteAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        $incidentID = str_replace('delete_', '', Input::get('delete_incident_id', null));
        $incident = Incident::getByID($incidentID);      

        $incidentCompanyID = Incident::getCompanyID($incident->incident_id);
        // Pull the value out of the JSON
        $incidentCompanyID = $incidentCompanyID['company_id']; 

        if($loggedUser->company_id == $incidentCompanyID || $loggedUser->isAdmin()) {
            $incident->deleted_at = date('Y-m-d H:i:s');
            $incident->save();

            return self::buildAjaxResponse(TRUE);
        }
        else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }

    // This function is a beast and could use separation...
    public function editIncidentAction(){
        $loggedUser = Auth::user();
        if (!Request::ajax()) {
            die('You are not authorized to perform this function. Your IP address has been logged.');
        }

        /*Code sends input to the application as JSON. You may access this data via Input::get like normal.*/
        $input = Input::json()->all();
        $incidentID = $input['incident_id'];
        
        $incident = Incident::find($incidentID);        
        $incidentCompanyID = Incident::getCompanyID($incident->incident_id);
        // Pull the value out of the JSON
        $incidentCompanyID = $incidentCompanyID['company_id'];
        
        $validationResult = Self::validateWebForm($input);
        if ($validationResult['result'] == FALSE) {
            // Validation result will be displayed to user in an alert box
            return self::buildAjaxResponse(FALSE, $validationResult['error']);
        }

        if($loggedUser->company_id == $incidentCompanyID || $loggedUser->isAdmin()) {
            $error = DB::transaction(function() use ($incident, $input){

                $incident->setFields($input);
                $incident->setActivities($input);

                if ($incident->save()) {
                    // Releases & Spills
                    if($incident->hasReleaseSpill()){
                        // USE THE SPILL ID FROM THE INCIDENT IN THE DB TO PREVENT TAMPERING
                        $releaseSpill = IncidentReleaseSpill::find($input['releaseSpill']['incident_release_spill_id']);
                        
                        if($releaseSpill->incident_id == $incident->incident_id){
                            $releaseSpill->setFields($input['releaseSpill']);
                            $releaseSpill->save();
                        }
                    }

                    // Medical Treatments -- this check is good enough for now since incident types are not currently editable
                    if($incident->shouldHaveTreatment()) {
                        foreach($input['treatments'] as $inputTreatment) {
                            $treatment = IncidentTreatment::find($inputTreatment['incident_treatment_id']);

                            if ($treatment instanceof IncidentTreatment && $treatment->incident_id == $incident->incident_id) {
                                $treatment->setFields($inputTreatment);
                                $treatment->save();

                                $currentPartStatementIds = array(); // will be used to determine what to delete later 
                                foreach($inputTreatment['parts'] as $inputPart){
                                    $part_statement_id = self::addOrEditIncidentTreatmentPartStatement($treatment, $inputPart);

                                    if($part_statement_id){
                                        array_push($currentPartStatementIds, $part_statement_id);
                                    }
                                }

                                // Remove deleted items
                                self::removeDeletedTreatmentPartStatements($treatment, $currentPartStatementIds);
                            }
                        }
                    }

                    // MVDs -- this check is good enough for now since incident types are not currently editable
                    if($incident->shouldHaveMVD()) {
                        foreach($input['mvds'] as $inputMvd) {
                            $mvd = IncidentMVD::find($inputMvd['incident_mvd_id']);

                            if ($mvd instanceof IncidentMVD && $mvd->incident_id == $incident->incident_id) {
                                $mvd->setFields($inputMvd);
                                $mvd->save();

                                $currentPartStatementIds = array(); // will be used to determine what to delete later 
                                foreach($inputMvd['parts'] as $inputPart){
                                    $part_statement_id = self::addOrEditIncidentMVDPartStatement($mvd, $inputPart);

                                    if($part_statement_id){
                                        array_push($currentPartStatementIds, $part_statement_id);
                                    }
                                }

                                // Remove deleted items
                                self::removeDeletedMVDPartStatements($mvd, $currentPartStatementIds);
                            }
                        }
                    }  

                    // INCIDENT PEOPLE
                    $currentPersonIds = array(); // will be used to determine what to delete later
                    foreach($input['persons'] as $inputPerson) {
                        
                        $personId = self::addOrEditIncidentPerson($incident, $inputPerson);

                        if($personId) {
                            array_push($currentPersonIds, $personId);
                        }

                    }
                    self::removeDeletedPersons($incident, $currentPersonIds);
                }

                // Solves an issue where a blank completed on is auto filled to 0000-00-00
                if($input['completed_on']=='0000-00-00'){
                    $incident->completed_on = NULL;
                }

                $incident->save();
            });

            if ($error == NULL) {
                return self::buildAjaxResponse(TRUE, 'Edit Successful.'); 
            } else {
                return self::buildAjaxResponse(FALSE, 'Edit Failed. Please try again. If the problem persists, please contact White Knight Support.');
            }
        } else {
             die('You are not authorized to perform this function. Your IP address has been logged.');
        } 
    }

    private function removeDeletedPersons($incident, $currentPersonIds){
        $oldPersons = $incident->persons()->get()->all();
        $oldPersonIds = array();

        //getting current persons in db 
        foreach ($oldPersons as $oldPerson) {
            $oldPersonId = $oldPerson->incident_person_id;
            array_push($oldPersonIds, $oldPersonId);
        }

        // array_diff This is used to get tasks which are still in the db which are not in the input. We will delete those tasks.
        $deletablePersonIDs = array_diff($oldPersonIds, $currentPersonIds);

        foreach ($deletablePersonIDs as $deletablePersonID) {         
            $incidentPerson = IncidentPerson::find($deletablePersonID);

            // Prevent tampering -- if the person exists and it has the same incident id as the verified incident
            if($incidentPerson && $incidentPerson->incident_id == $incident->incident_id){
                $incidentPerson->delete();
            } 
        }
    }

    private function addOrEditIncidentPerson($incident, $inputPerson) {

        $person = IncidentPerson::firstOrNew(array('incident_person_id' => $inputPerson['incident_person_id'],
                                                   'incident_id' => $incident->incident_id));
        // TODO: check if employment status and type are valid
        if($person instanceof IncidentPerson &&  $person->incident_id == $incident->incident_id){
            $person->setFields($inputPerson);
            if($person->type == IncidentPerson::TYPE_WITNESS || $person->type == IncidentPerson::TYPE_3RD_PARTY) {
                $person->time_on_shift = '';
                $person->time_of_incident = '';
                $person->ts_on_shift = NULL;
                $person->ts_of_incident = NULL;
            }
            if($person->save()){
                return $person->incident_person_id;
            }
        }

    }

    private function removeDeletedMVDPartStatements($mvd, $currentPartStatementIds){
        $oldParts = $mvd->statements()->get()->all();
        $oldPartIds = array();
        foreach ($oldParts as $oldPart) {
            $oldStatementPartID = $oldPart->part_statement_id;
            array_push($oldPartIds, $oldStatementPartID);
        }

        // array_diff This is used to get tasks which are still in the db which are not in the input. We will delete those tasks.
        $deletablePartStatementIDs = array_diff($oldPartIds, $currentPartStatementIds);

        foreach ($deletablePartStatementIDs as $partStatementId) {            
            $incidentPartStatement = IncidentPartStatement::find($partStatementId);

            // Prevent tampering -- if the part statement exists and it has the same incident id as the verified incident
            if($incidentPartStatement && $incidentPartStatement->incident_id == $mvd->incident_id){
                foreach ($incidentPartStatement->photos as $photo) {
                    self::deleteIncidentPartStatementPhoto($photo->name);
                }

                $incidentPartStatement->delete();
            } 
        }
    }

    // Helper function which removes the user specified tasks from the DB
    private function removeDeletedTreatmentPartStatements ($treatment, $currentPartStatementIds) {
        $oldParts = $treatment->statements()->get()->all();
        $oldPartIds = array();
        foreach ($oldParts as $oldPart) {
            $oldStatementPartID = $oldPart->part_statement_id;
            array_push($oldPartIds, $oldStatementPartID);
        }

        // array_diff This is used to get tasks which are still in the db which are not in the input. We will delete those tasks.
        $deletablePartStatementIDs = array_diff($oldPartIds, $currentPartStatementIds);

        foreach ($deletablePartStatementIDs as $partStatementId) {
            $incidentPartStatement = IncidentPartStatement::find($partStatementId);

            // Prevent tampering -- if the part statement exists and it has the same incident id as the verified incident
            if($incidentPartStatement && $incidentPartStatement->incident_id == $treatment->incident_id){
                foreach ($incidentPartStatement->photos as $photo) {
                    self::deleteIncidentPartStatementPhoto($photo->name);
                }

                $incidentPartStatement->delete();
            }
        }
    }

    private function deleteIncidentPartStatementPhoto($photoId) {
        $photo = Photo::getPhotoByPhotoName($photoId);
        $storageManager = new StorageManager();

        if ($photo->delete()) {
            $storageManager->deletePhoto($photo);
            return TRUE;
        }
        return FALSE;
    }


    private function addOrEditIncidentMVDPartStatement($mvd, $inputPartStatement) {
        $incidentSchemaPart = IncidentSchemaPart::find($inputPartStatement['incident_schema_part_id']);

        // Checking that it is a valid part
        if($incidentSchemaPart) {
            $incidentSchemaType = $incidentSchemaPart->schema->type;
            $statementPartId = $inputPartStatement['part_statement_id'];

            if ($incidentSchemaType == IncidentSchema::TYPE_TRUCK OR $incidentSchemaType == IncidentSchema::TYPE_TRAILER) {
                $stmt = IncidentPartStatement::firstOrNew(array('incident_id' => $mvd->incident_id,
                                                                'part_statement_id' => $statementPartId,
                                                                'statementable_id' => $mvd->incident_mvd_id,
                                                                'statementable_type' => 'IncidentMVD'));
                // Edit values
                $stmt->comment = $inputPartStatement['comment'];
                $stmt->incident_schema_part_id = $inputPartStatement['incident_schema_part_id'];
                if($stmt->save()){
                    return $stmt->part_statement_id;
                }
            }
        }
    }


    private function addOrEditIncidentTreatmentPartStatement($treatment, $inputPartStatement) {

        $incidentSchemaPart = IncidentSchemaPart::find($inputPartStatement['incident_schema_part_id']);

        // Checking that it is a valid part
        if($incidentSchemaPart) {
            $incidentSchemaType = $incidentSchemaPart->schema->type;
            $statementPartId = $inputPartStatement['part_statement_id'];

            if ($incidentSchemaType == IncidentSchema::TYPE_BODY) {
                $stmt = IncidentPartStatement::firstOrNew(array('part_statement_id' => $statementPartId,
                                                                'incident_id' => $treatment->incident_id,
                                                                'statementable_id' => $treatment->incident_treatment_id,
                                                                'statementable_type' => 'IncidentTreatment'));
                // Edit values
                $stmt->comment = $inputPartStatement['comment'];
                $stmt->incident_schema_part_id = $inputPartStatement['incident_schema_part_id'];
                if($stmt->save()){
                    return $stmt->part_statement_id;
                }
            }
        }
    }


    // This functionality should NOT be in the controller.
    // Don't know how to implement a better architecture right now.
    private function validateWebForm($input){
        // General Incident fields
        $rules = array('title' => 'required|max:100',
                       'lsd' => 'max:50|regex:/\d\d\d\/\d\d-\d\d-\d\d\d-\d\d-W\dM/',
                       'source_receiver_line'=> 'max:100',
                       'location' => 'max:400',
                       'specific_area' => 'max:400',
                       'road' => 'max:400',
                       'description' => 'max:1000|required',
                       'root_cause' => 'max:1000|required',
                       'immediate_action' => 'max:1000|required',
                       'corrective_action' => 'max:1000|required',
                       'corrective_action_implementation' => 'max:1000',
                       'completed_on' => 'regex:/\d\d\d\d-\d\d-\d\d/',
                       'action' => 'max:500'
                       );

        //latitude and longitude could/should also be checked here

        $messages = array();

        // Object fields =========

        // Release / Spills
        if (isset($input['releaseSpill'])) {
            $rules['releaseSpill.commodity'] = 'required|max:200';
            $rules['releaseSpill.release_source'] = 'max:500';
            $rules['releaseSpill.release_to'] = 'max:500';
            $rules['releaseSpill.quantity_released'] = 'numeric';
            $rules['releaseSpill.quantity_released_unit'] = 'numeric';
            $rules['releaseSpill.quantity_recovered'] = 'numeric';
            $rules['releaseSpill.quantity_recovered_unit'] = 'numeric';
            $rules['releaseSpill.comment'] = 'max:1000';
        }

        // TREATMENTS
        if (isset($input['treatments'])) {
            $treatmentCount = 0;
            foreach($input['treatments'] as $treatment) {

                $rules['treatments.' . $treatmentCount . '.responder_name'] = 'required|max:100';
                $rules['treatments.' . $treatmentCount . '.responder_company'] = 'required|max:500';
                $rules['treatments.' . $treatmentCount . '.responder_phone_number'] = 'required|max:50';
                $rules['treatments.' . $treatmentCount . '.comment'] = 'max:1000';

                // Checking to make sure that no parts have the same part type
                $partTypes = array();
                if (isset($treatment['parts'])){ 
                    foreach($treatment['parts'] as $part){
                        array_push($partTypes, $part['incident_schema_part_id']);
                    }  
                } 

                // Checking if they picked ducpliate part types (not allowed)
                if(count(array_unique($partTypes))<count($partTypes)){
                    $message = array();
                    array_push($message, 'In your Medical Treatments ('.$treatment['type']['type_name'].'), you may not have duplicate injured body parts (ex. two different parts listed as "Back Head"). Each specified part must be unique.');
                    return array('result'=>FALSE, 'error'=>$message);
                } 

                $treatmentCount++;  
            }  
        }

        // MOTOR VEHICLE DAMAGE
        if (isset($input['mvds'])) {
            $mvdCount = 0;
            foreach($input['mvds'] as $mvd) {
                $rules['mvds.' . $mvdCount . '.driver_license_number'] = 'required|max:100';
                $rules['mvds.' . $mvdCount . '.insurance_company'] = 'required|max:500';
                $rules['mvds.' . $mvdCount . '.insurance_policy_number'] = 'required|max:500';
                $rules['mvds.' . $mvdCount . '.policy_expiry_date'] = 'required|max:50'; // could also specify date format
                $rules['mvds.' . $mvdCount . '.vehicle_year'] = 'numeric';
                $rules['mvds.' . $mvdCount . '.make'] = 'max:100';
                $rules['mvds.' . $mvdCount . '.model'] = 'max:300';
                $rules['mvds.' . $mvdCount . '.vin'] = 'required|max:100';
                $rules['mvds.' . $mvdCount . '.color'] = 'max:50';
                $rules['mvds.' . $mvdCount . '.license_plate'] = 'required|max:100';
                $rules['mvds.' . $mvdCount . '.time_of_incident'] = 'required|max:50';
                $rules['mvds.' . $mvdCount . '.vehicles_involved'] = 'required|numeric|min:0|max:99';

                if($mvd['wearing_seatbelts'] == 0) {
                    $rules['mvds.' . $mvdCount . '.wearing_seatbelts_description'] = 'required|max:300';
                }
                
                // Checking to make sure that no parts have the same part type
                $partTypes = array();
                if (isset($mvd['parts'])){ 
                    foreach($mvd['parts'] as $part){
                        array_push($partTypes, $part['incident_schema_part_id']);
                    }  
                } 

                // Checking if they picked ducpliate part types (not allowed)
                if(count(array_unique($partTypes))<count($partTypes)){
                    $message = array();
                    array_push($message, 'In your Motor Vehicle Damage ('.$mvd['type']['type_name'].'), you may not have duplicate damaged vehicle parts (ex. two different parts listed as "Roof"). Each specified part must be unique.');
                    return array('result'=>FALSE, 'error'=>$message);
                } 
             
                $mvdCount++;  
            }  
        }

        // INCIDENT PERSONS

        // Copied from IncidentPerson
        define('EMPLOYEE_STATUS_EMPLOYEE', 0);
        define('EMPLOYEE_STATUS_SUB', 1);
        define('EMPLOYEE_STATUS_PRIME', 2);
        define('EMPLOYEE_STATUS_BYSTANDER', 3);

        define('TYPE_WORKER', 0);
        define('TYPE_WITNESS', 1);
        define('TYPE_3RD_PARTY', 2);

        if (isset($input['persons'])) {
            $personCount = 0;
            foreach($input['persons'] as $person){
                if($person['type'] == TYPE_WORKER) {
                    $rules['persons.' . $personCount . '.company'] = 'required|max:200';
                    $rules['persons.' . $personCount . '.time_on_shift'] = 'required';
                    $rules['persons.' . $personCount . '.time_of_incident'] = 'required';
                }

                // Rules which apply to all people
                $rules['persons.' . $personCount . '.first_name'] = 'required|max:100';
                $rules['persons.' . $personCount . '.last_name'] = 'required|max:100';
                $rules['persons.' . $personCount . '.phone_number'] = 'required|max:50|regex:/\d\d\d-\d\d\d-\d\d\d\d/';
                $rules['persons.' . $personCount . '.statement'] = 'required|max:1000';

                $personCount++;
            }    
        } 

        // Begin validation
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()){
            // Markup can be placed in the :message area should we ever change the display format
            return array('result'=>FALSE, 'error'=>$validator->messages()->all(':message'));
        }

        return array('result'=>TRUE);
    } 
    
}

<?php

class StorageManager {

    public function __construct() {
        $this->storagePaths = Config::get('api::storagePaths');
    }

    private function buildOrganizationalTree($object) {
        $dataPath = $this->storagePaths['companyLevel'];
        $group = Group::find($object->group_id);
        $companyId = $group->businessUnit->division->company_id;
        $divisionId = $group->businessUnit->division_id;
        $BuId = $group->business_unit_id;
        $path = ("{$dataPath}/{$companyId}/{$divisionId}/{$BuId}/{$object->group_id}");
        if (!is_dir($path)) {
            //create the organizational tree recursively
            mkdir($path, 0777, TRUE);

            //create the workers directory
            $workersPath = "{$path}/workers";
            mkdir($workersPath);
            //create the vehicles directory
            $vehiclessPath = "{$path}/vehicles";
            mkdir($vehiclessPath);

            //craete the daily signin directory
            $signins = "{$path}/daily-signins";
            mkdir($signins);
        } else {
            $workersPath = "{$path}/workers";
            $vehiclessPath = "{$path}/vehicles";
            $signins = "{$path}/daily-signins";
        }
        return array('workers' => $workersPath,
            'vehicles' => $vehiclessPath,
            'daily-signins' => $signins);
    }

    private function buildWorkerDirs($dir) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (!is_dir("{$dir}/signatures")) {
            mkdir("{$dir}/signatures");
        }
        if (!is_dir("{$dir}/hazards")) {
            mkdir("{$dir}/hazards");
        }
        if (!is_dir("{$dir}/near-misses")) {
            mkdir("{$dir}/near-misses");
        }
        if (!is_dir("{$dir}/positive-observations")) {
            mkdir("{$dir}/positive-observations");
        }
        if (!is_dir("{$dir}/flha")) {
            mkdir("{$dir}/flha");
        }
        if (!is_dir("{$dir}/tailgate")) {
            mkdir("{$dir}/tailgate");
        }
        if (!is_dir("{$dir}/incidents")) {
            mkdir("{$dir}/incidents");
        }
    }

    private function buildDirTreeForWorker(Worker $worker) {
        $paths = $this->buildOrganizationalTree($worker);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$worker->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        return $thisWorkerPath;
    }

    private function buildDirTreeForHazard(Hazard $hazard) {
        $paths = $this->buildOrganizationalTree($hazard->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$hazard->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $hazardPath = "{$thisWorkerPath}/hazards/{$hazard->hazard_id}";
        if (!is_dir($hazardPath)) {
            mkdir($hazardPath);
        }
        return $hazardPath;
    }

    private function buildDirTreeForNearMiss(NearMiss $nearMiss) {
        $paths = $this->buildOrganizationalTree($nearMiss->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$nearMiss->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $nearMissesPath = "{$thisWorkerPath}/near-misses/{$nearMiss->near_miss_id}";
        if (!is_dir($nearMissesPath)) {
            mkdir($nearMissesPath);
        }
        return $nearMissesPath;
    }

    private function buildDirTreeForPositiveObservation(PositiveObservation $positiveObservation) {
        $paths = $this->buildOrganizationalTree($positiveObservation->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$positiveObservation->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $positiveObservationPath = "{$thisWorkerPath}/positive-observations/{$positiveObservation->positive_observation_id}";
        if (!is_dir($positiveObservationPath)) {
            mkdir($positiveObservationPath);
        }
        return $positiveObservationPath;
    }

    private function buildDirTreeForFlha(Flha $flha) {
        $paths = $this->buildOrganizationalTree($flha->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$flha->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $flhaPath = "{$thisWorkerPath}/flha/{$flha->flha_id}";
        if (!is_dir($flhaPath)) {
            mkdir($flhaPath);
        }
        return $flhaPath;
    }

    private function buildDirTreeForTailgate(Tailgate $tailgate) {
        $paths = $this->buildOrganizationalTree($tailgate->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$tailgate->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $tailgatePath = "{$thisWorkerPath}/tailgate/{$tailgate->tailgate_id}";
        if (!is_dir($tailgatePath)) {
            mkdir($tailgatePath);
        }
        return $tailgatePath;
    }
    
    private function buildDirTreeForIncident(Incident $incident) {
        $paths = $this->buildOrganizationalTree($incident->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$incident->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $incidentPath = "{$thisWorkerPath}/incidents/{$incident->incident_id}";
        if (!is_dir($incidentPath)) {
            mkdir($incidentPath);
        }
        if (!is_dir($incidentPath.'/mvd')) {
            mkdir($incidentPath.'/mvd');
        }
        return $incidentPath;
    }
    
    private function buildDirTreeForIncidentStatement(IncidentPartStatement $stmt) {
        $incident = $stmt->incident;
        $paths = $this->buildOrganizationalTree($incident->addedBy);
        $workersPath = $paths['workers'];
        $thisWorkerPath = "{$workersPath}/{$incident->worker_id}";
        $this->buildWorkerDirs($thisWorkerPath);
        $incidentPath = "{$thisWorkerPath}/incidents/{$incident->incident_id}";
        if (!is_dir($incidentPath)) {
            mkdir($incidentPath);
        }
        $stmtPath = "{$incidentPath}/{$stmt->part_statement_id}";
        if (!is_dir($stmtPath)) {
            mkdir($stmtPath);
        }
        return $stmtPath;
    }

    private function buildDirTreeForSpotcheck(Spotcheck $spotcheck) {
        $flhaPath = $this->buildDirTreeForFlha($spotcheck->flha);
        $spotchecksDirPath = "{$flhaPath}/spotchecks";
        if (!is_dir($spotchecksDirPath)) {
            mkdir($spotchecksDirPath);
        }
        $spotcheckPath = "$spotchecksDirPath/{$spotcheck->spotcheck_id}";
        if (!is_dir($spotcheckPath)) {
            mkdir($spotcheckPath);
        }
        return $spotcheckPath;
    }

    private function buildDirTreeForSignoffVisitor(SignoffVisitor $visitor) {
        $flhaPath = $this->buildDirTreeForFlha($visitor->flha);
        $visitorSignoffs = "{$flhaPath}/visitor-signoffs";
        if (!is_dir($visitorSignoffs)) {
            mkdir($visitorSignoffs);
        }
        $visitorPath = "$visitorSignoffs/{$visitor->signoff_visitor_id}";
        if (!is_dir($visitorPath)) {
            mkdir($visitorPath);
        }
        return $visitorPath;
    }

    private function buildDirTreeForTailgateSignoffVisitor(TailgateSignoffVisitor $visitor) {
        $tailgatePath = $this->buildDirTreeForTailgate($visitor->tailgate);
        $visitorSignoffs = "{$tailgatePath}/visitor-signoffs";
        if (!is_dir($visitorSignoffs)) {
            mkdir($visitorSignoffs);
        }
        $visitorPath = "$visitorSignoffs/{$visitor->signoff_visitor_id}";
        if (!is_dir($visitorPath)) {
            mkdir($visitorPath);
        }
        return $visitorPath;
    }

    private function buildDirTreeForSignoffWorker(SignoffWorker $signoffWorker) {
        $flhaPath = $this->buildDirTreeForFlha($signoffWorker->flha);
        $workerSignoffs = "{$flhaPath}/worker-signoffs";
        if (!is_dir($workerSignoffs)) {
            mkdir($workerSignoffs);
        }
        $workerPath = "$workerSignoffs/{$signoffWorker->signoff_worker_id}";
        if (!is_dir($workerPath)) {
            mkdir($workerPath);
        }
        return $workerPath;
    }

    private function buildDirTreeForTailgateSignoffWorker(TailgateSignoffWorker $signoffWorker) {
        $tailgatePath = $this->buildDirTreeForTailgate($signoffWorker->tailgate);
        $workerSignoffs = "{$tailgatePath}/worker-signoffs";
        if (!is_dir($workerSignoffs)) {
            mkdir($workerSignoffs);
        }
        $workerPath = "$workerSignoffs/{$signoffWorker->signoff_worker_id}";
        if (!is_dir($workerPath)) {
            mkdir($workerPath);
        }
        return $workerPath;
    }

    private function buildDirTreeForSignins(DailySignin $signin) {
        $groupPath = $this->buildOrganizationalTree($signin);
        $dailySignin = $groupPath['daily-signins'];
        if (!is_dir($dailySignin)) {
            mkdir($dailySignin);
        }
        return $dailySignin;
    }

    private function buildDirTreeForVehicle(Vehicle $vehicle) {
        $organizationalTreeObj = $vehicle->getVehicleOrganizationalTree();
        $paths = $this->buildOrganizationalTree($organizationalTreeObj);
        $vehiclesPath = $paths['vehicles'];
        $thisVehiclePath = "{$vehiclesPath}/{$vehicle->vehicle_id}";
        if (!is_dir($thisVehiclePath)) {
            mkdir($thisVehiclePath, 0777, true);
        }
        return $thisVehiclePath;
    }

    private function buildDirTreeForInspection(Inspection $inspection) {
        $vehiclePath = $this->buildDirTreeForVehicle($inspection->vehicle);
        $inspectionsPath = "{$vehiclePath}/inspections";
        if (!is_dir($inspectionsPath)) {
            mkdir($inspectionsPath, 0777, true);
        }
        $thisInspectionPath = "{$inspectionsPath}/{$inspection->inspection_id}";
        if (!is_dir($thisInspectionPath)) {
            mkdir($thisInspectionPath, 0777);
        }
        return $thisInspectionPath;
    }

    public function saveProfilePhoto($worker, $photo) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoPath = $photo->move($path, 'profile.jpg');
        self::genereateProfileThumbnail($photoPath->getPathName());
    }

    public function getProfilePhoto($worker) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoPath = "{$path}/profile.jpg";
        return $photoPath;
    }

    public function getThumbProfilePhoto($worker) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoThumbPath = "{$path}/thumb_profile.jpg";
        if (!file_exists($photoThumbPath)) {
            $photoPath = "{$path}/profile.jpg";
            self::genereateProfileThumbnail($photoPath);
        }
        return $photoThumbPath;
    }

    public function saveSignaturePhoto($worker, $photo) {
        $path = $this->buildDirTreeForWorker($worker);
        do {
            $signatureName = uniqid();
            $photoPath = "{$path}/signatures";
        } while (is_file($photoPath));
        $photo->move($photoPath, $signatureName);
        return $signatureName;
    }

    public function getSignaturePhoto($worker, $signatureId) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoPath = "{$path}/signatures/{$signatureId}";
        return $photoPath;
    }

    public function deleteSignaturePhoto($worker, $signatureId) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoPath = "{$path}/signatures/{$signatureId}";
        if (!is_file($photoPath)) {
            return false;
        } else {
            unlink($photoPath);
            return true;
        }
    }

    public function deleteTicketFolder($companyId, $workerId, $ticketId) {
        $success = false;
        $dataPath = $this->storagePaths['companyLevel'];
        $path = ("{$dataPath}/{$companyId}/tickets/{$workerId}/{$ticketId}");

        if (is_dir($path)) {
            system('rm -rf ' . escapeshellarg($path), $retval); // for escapeshellarg() for security
            $success = ($retval == 0); // UNIX commands return zero on success
        }

        return $success;
    }

    public function getSignatureListForWorker($worker) {
        $path = $this->buildDirTreeForWorker($worker);
        $photoPath = "{$path}/signatures/";
        return array_values(array_diff(scandir($photoPath), array('..', '.')));
    }

    public function saveVehiclePhoto($vehicle, $vehiclePhoto) {

        $path = $this->buildDirTreeForVehicle($vehicle);
        $vehiclePhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$vehiclePhotoName}";
        $vehiclePhoto->move($path, $vehiclePhotoName);
        return array('path' => $photoPath, 'name' => $vehiclePhotoName);
    }

    public function getPhotoListForVehicle($vehicle) {
        $path = $this->buildDirTreeForVehicle($vehicle);
        return array_values(array_diff(scandir($path), array('..', '.', 'inspections')));
    }

    public function saveInspectionPhoto(Inspection $inspection, $inspectionPhoto) {
        $path = $this->buildDirTreeForInspection($inspection);
        $inspectionPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$inspectionPhotoName}";

        $inspectionPhoto->move($path, $inspectionPhotoName);

        return array('path' => $photoPath, 'name' => $inspectionPhotoName);
    }

    public function saveHazardPhoto($hazard, $hazardPhoto) {
        $path = $this->buildDirTreeForHazard($hazard);
        $hazardPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$hazardPhotoName}";

        $hazardPhoto->move($path, $hazardPhotoName);

        return array('path' => $photoPath, 'name' => $hazardPhotoName);
    }

    public function saveNearMissPhoto($nearMiss, $nearMissPhoto) {
        $path = $this->buildDirTreeForNearMiss($nearMiss);
        $nearMissPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$nearMissPhotoName}";

        $nearMissPhoto->move($path, $nearMissPhotoName);

        return array('path' => $photoPath, 'name' => $nearMissPhotoName);
    }

    public function savePositiveObservationPhoto(PositiveObservation $positiveObservation, $positiveObservationPhoto) {
        $path = $this->buildDirTreeForPositiveObservation($positiveObservation);
        $poPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$poPhotoName}";
        $positiveObservationPhoto->move($path, $poPhotoName);
        return array('path' => $photoPath, 'name' => $poPhotoName);
    }

    public function saveFlhaPhoto(Flha $flha, $flhaPhoto) {
        $path = $this->buildDirTreeForFlha($flha);
        $flhaPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$flhaPhotoName}";

        $flhaPhoto->move($path, $flhaPhotoName);

        return array('path' => $photoPath, 'name' => $flhaPhotoName);
    }

    public function saveSpotcheckSignaturePhoto(Spotcheck $spotcheck, $photo) {
        $path = $this->buildDirTreeForSpotcheck($spotcheck);
        $photo->move($path, "signature");
    }

    public function getSpotcheckSignaturePhoto(Spotcheck $spotcheck) {
        $path = $this->buildDirTreeForSpotcheck($spotcheck);
        $photoPath = "{$path}/signature";
        return $photoPath;
    }

    public function saveSignoffVisitorPhoto(SignoffVisitor $visitor, $photo) {
        $path = $this->buildDirTreeForSignoffVisitor($visitor);
        $photo->move($path, 'photo');
    }

    public function getSignoffVisitorPhoto(SignoffVisitor $visitor) {
        $path = $this->buildDirTreeForSignoffVisitor($visitor);
        $photoPath = "{$path}/photo";
        return $photoPath;
    }

    public function saveSignoffVisitorSignature(SignoffVisitor $visitor, $photo) {
        $path = $this->buildDirTreeForSignoffVisitor($visitor);
        $photo->move($path, 'signature');
    }

    public function getSignoffVisitorSignature(SignoffVisitor $visitor) {
        $path = $this->buildDirTreeForSignoffVisitor($visitor);
        $photoPath = "{$path}/signature";
        return $photoPath;
    }

    public function saveTailgateSignoffVisitorSignature(TailgateSignoffVisitor $visitor, $photo) {
        $path = $this->buildDirTreeForTailgateSignoffVisitor($visitor);
        $photo->move($path, 'signature');
    }

    public function getTailgateSignoffVisitorSignature(TailgateSignoffVisitor $visitor) {
        $path = $this->buildDirTreeForTailgateSignoffVisitor($visitor);
        $photoPath = "{$path}/signature";
        return $photoPath;
    }

    public function saveSignoffWorkerPhoto(SignoffWorker $worker, $photo) {
        $path = $this->buildDirTreeForSignoffWorker($worker);
        $photo->move($path, 'photo');
    }

    public function getSignoffWorkerPhoto(SignoffWorker $worker) {
        $path = $this->buildDirTreeForSignoffWorker($worker);
        $photoPath = "{$path}/photo";
        return $photoPath;
    }

    public function saveSignoffWorkerSignature(SignoffWorker $worker, $photo) {
        $path = $this->buildDirTreeForSignoffWorker($worker);
        $photo->move($path, 'signature');
    }

    public function getSignoffWorkerSignature(SignoffWorker $worker) {
        $path = $this->buildDirTreeForSignoffWorker($worker);
        $photoPath = "{$path}/signature";
        return $photoPath;
    }

    public function saveTailgateSignoffWorkerSignature(TailgateSignoffWorker $worker, $photo) {
        $path = $this->buildDirTreeForTailgateSignoffWorker($worker);
        $photo->move($path, 'signature');
    }

    public function getTailgateSignoffWorkerSignature(TailgateSignoffWorker $worker) {
        $path = $this->buildDirTreeForTailgateSignoffWorker($worker);
        $photoPath = "{$path}/signature";
        return $photoPath;
    }

    public function saveDailySigninSignature(DailySignin $signin, $photo) {
        $path = $this->buildDirTreeForSignins($signin);
        $photo->move($path, $signin->daily_signin_id);
    }

    public function getDailySigninSignature(DailySignin $signin) {
        $path = $this->buildDirTreeForSignins($signin);
        $photoPath = "{$path}/{$signin->daily_signin_id}";
        return $photoPath;
    }

    public function saveIncidentStatementPhoto($stmt, $stmtPhoto) {
        $path = $this->buildDirTreeForIncidentStatement($stmt);
        $stmtPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$stmtPhotoName}";
        $stmtPhoto->move($path, $stmtPhotoName);
        return array('path' => $photoPath, 'name' => $stmtPhotoName);
    }
    
    public function saveIncidentPhoto($incident, $incidentPhoto) {
        $path = $this->buildDirTreeForIncident($incident);
        $incidentPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$incidentPhotoName}";

        $incidentPhoto->move($path, $incidentPhotoName);

        return array('path' => $photoPath, 'name' => $incidentPhotoName);
    }
    
    public function saveIncidentMVDPhoto($incidentMVD, $incidentPhoto) {
        $path = $this->buildDirTreeForIncident($incidentMVD->incident).'/mvd';
        $incidentPhotoName = Photo::generatePhotoName();
        $photoPath = "{$path}/{$incidentPhotoName}";

        $incidentPhoto->move($path, $incidentPhotoName);

        return array('path' => $photoPath, 'name' => $incidentPhotoName);
    }
    
    
    public function deletePhoto(Photo $photo) {
        $photoPath = $photo->path;
        if (!is_file($photoPath)) {
            return false;
        } else {
            unlink($photoPath);
            return true;
        }
    }

    public static function noPhoto() {
        $sm = new self();
        return $sm->storagePaths['nophoto'];
    }

    public static function saveTempHazard($pdf, $hazard) {
        $filename = $hazard->hazard_id . ' ' . $hazard->title . " " . $hazard->created_at;
        return self::saveTempResource($pdf, $filename);
    }
    
    public static function saveTempIncident($pdf, $incident) {
        $filename = $incident->incident_id . ' ' . $incident->title . " " . $incident->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempNearMiss($pdf, $nearMiss) {
        $filename = $nearMiss->near_miss_id . ' ' . $nearMiss->title . " " . $nearMiss->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempPO($pdf, $po) {
        $filename = $po->positive_observation_id . ' ' . $po->title . " " . $po->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempFlha($pdf, $flha) {
        $filename = $flha->flha_id . ' ' . $flha->title . " " . $flha->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempTailgate($pdf, $tailgate) {
        $filename = $tailgate->tailgate_id . ' ' . $tailgate->title . " " . $tailgate->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempInspection($pdf, $vehicle, $inspection) {
        $filename = $vehicle->vehicle_id . ' ' . $inspection->inspection_id . " " . $inspection->created_at;
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempDailySignin($pdf, $filename) {
        return self::saveTempResource($pdf, $filename);
    }
    
    public static function saveTempStats($pdf, $filename) {
        return self::saveTempResource($pdf, $filename);
    }

    public static function saveTempPDFDocument($pdf, $filename) {
        return self::saveTempResource($pdf, $filename);
    }

    private static function saveTempResource($pdf, $filename) {
        $cls = Config::get('api::storagePaths.companyLevel');
        $filename = "$cls/$filename.pdf";
        $pdf->save($filename);
        return $filename;
    }

    public static function genereateProfileThumbnail($path) {
        $profileThumbPath = str_replace('profile', 'thumb_profile', $path);
        $command = "convert $path -resize 100x100 $profileThumbPath";
        shell_exec($command);
    }

    public static function getIncidentSchema($schema, $size) {
        $dir = Config::get('api::storagePaths.incidentSchemaPhotos');
        $filename = "{$dir}/{$size}_{$schema->key}";
        return $filename;
    }
    
    
    public function saveAdminSignature(Admin $admin, $photo) {
        $dataPath = $this->storagePaths['companyLevel'];
        $path = "{$dataPath}/admin-signatures/";
        if (!is_dir($path)) {
            //create the directory 
            mkdir($path, 0777, TRUE);
        }
        $photoPath = "{$path}/{$admin->admin_id}";
        File::put($photoPath,$photo);
    }
        
    public function getAdminSignature($adminId) {
        $dataPath = $this->storagePaths['companyLevel'];
        $path = "{$dataPath}/admin-signatures/{$adminId}";
        if (is_file($path)) {
            return $path;
        }
        return false;
    }
}

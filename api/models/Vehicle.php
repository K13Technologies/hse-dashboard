<?php

class Vehicle extends Eloquent {

    protected $table = 'vehicles';
    protected $primaryKey = 'vehicle_id';
    public $timestamps = false;
    public $fillable = array('license_plate', 'vehicle_number', 'color', 'mileage', 'worker_id', 'company_id');
    private static $properties = array('license_plate', 'vehicle_number', 'color', 'mileage', 'make', 'model');

    /**
     * Returns the vehicle
     * @param int $vehicleID
     * @return Tailgate returns a vehicle if exists or NULL if it doesn't;
     */
    public static function getByID($vehicleID) {
        $result = self::where('vehicle_id', '=', $vehicleID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the vehicle
     * @param int $vehicleID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($vehicleID) {  
        $result = self::where('vehicle_id', '=', $vehicleID)
                        ->join('workers', 'workers.worker_id', '=', 'vehicles.worker_id')
                        ->select('workers.company_id')
                        ->get();

        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public function group() {
        return $this->belongsTo('Group', 'group_id');
    }

    public function company() {
        return $this->belongsTo('Company', 'company_id');
    }

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function inspections() {
        return $this->hasMany('Inspection', 'vehicle_id');
    }

    public function outstandingInspections() {
        return $this->hasMany('Inspection', 'vehicle_id')->where('action_required', '=', '1');
    }

    public function completedInspections() {
        return $this->hasMany('Inspection', 'vehicle_id')
                        ->where('action_required', '=', '0');
    }

    private function latestInspections($workerId) {
        return $this->inspections()
                    ->where('worker_id', '=', $workerId)
                    ->whereRaw(ApiController::$interval)->get();
    }

    public function latestInspectionsCount() {
        return $this->inspections()->whereRaw(ApiController::$interval)->count();
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function setFields(array $input, Worker $addedBy = NULL) {

        if ($addedBy instanceof Worker) {
            $this->worker_id = $addedBy->worker_id;
            $this->group_id = $this->addedBy->group_id;
            $this->company_id = $this->addedBy->company_id;
        }
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)) {
                $this->$key = $val;
            }
        }
        return $this;
    }

    public static function getByLicensePlate($licensePlate) {
        $result = self::where('license_plate', '=', $licensePlate)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public static function getByIdentificatioNumber($identificationNumber) {
        $result = self::where('vehicle_number', '=', $identificationNumber)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public static function listAllInGroup($groupId) {
        $result = Vehicle::where('group_id', '=', $groupId)->get()
                            ->where('deleted_at', '=', NULL);

        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;
    }

    public static function listAllInCompany($companyId) {
        $result = Vehicle::where('company_id', '=', $companyId)
                    ->where('deleted_at', '=', NULL)
                    ->get();

        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;
    }

    public static function getVehicleWithRecentInspections($vehicleId, $workerId) {

        $vehicle = self::find($vehicleId);
        $newInspectionList = array();
        $inspectionList = array();

        $latestInspections = $vehicle->latestInspections($workerId)->all();
        foreach ($latestInspections as $inspection) {
            $inspection->photoList = $inspection->extractPhotoIds();

            if (!$inspection->has_nulls) {
                $inspectionList[] = $inspection->toArray();
            }

            $newInspectionList[] = $inspection->toArrayNoNulls();
        }
        $photoList = $vehicle->getPhotoIdList();
        $vehicle->inspectionList = $inspectionList;
        $vehicle->newInspectionList = $newInspectionList;
        $vehicle->photoList = $photoList;
        return $vehicle;
    }

    public function getVehicleOrganizationalTree() {
        $vehicleGroup = $this->group;
        $vehicleBU = $vehicleGroup->businessUnit;
        $vehicleDivision = $vehicleBU->division;
        $vehicleCompany = $vehicleDivision->company;

        $result = new stdClass();
        $result->company_id = $vehicleCompany->company_id;
        $result->division_id = $vehicleDivision->division_id;
        $result->business_unit_id = $vehicleBU->business_unit_id;
        $result->group_id = $this->group_id;

        return $result;
    }

    private function getPhotoIdList() {
        $sm = new StorageManager();
        $list = $sm->getPhotoListForVehicle($this);
        return $list;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('group.businessUnit.division.company', 'inspections', 'outstandingInspections', 'completedInspections.actionsCompleted')
                            ->orderBy('updated_at', 'desc')->get()->all();
        } else {
            $companyGroups = Group::getAllForCompany($admin->company_id);
            if(!$companyGroups){
                return [];
            } 
            return self::with('group.businessUnit.division.company', 'inspections', 'outstandingInspections', 'completedInspections.actionsCompleted')
                            ->whereIn('group_id', $companyGroups)
                            ->orderBy('updated_at', 'desc')->get()->all();
        }
    }

    public static function getTotalInspectionCount(Admin $admin) {
        return self::where('vehicles.company_id', $admin->company_id)
                   ->where('vehicles.deleted_at', '=', NULL)
                   ->join('inspections', 'inspections.vehicle_id', '=', 'vehicles.vehicle_id')
                   ->get()
                   ->count();     
    }

    public static function getAllInspections(Admin $admin) {
        return self::where('vehicles.company_id', $admin->company_id)
                   ->where('vehicles.deleted_at', '=', NULL)
                   ->join('inspections', 'inspections.vehicle_id', '=', 'vehicles.vehicle_id')
                   ->get()
                   ->all();     
    }

    public static function getMissedInspectionsVehicleList(Admin $admin) {
        $res = array();
        $companyGroups = Group::getAllForCompany($admin->company_id);
        if (empty($companyGroups)) {
            return $res;
        }
        $vehicles = self::with('group.businessUnit.division.company')->whereIn('group_id', $companyGroups)->get()->all();
        foreach ($vehicles as $v) {
            if ($v->latestInspectionsCount() == 0 OR
                    $v->outstandingInspections()->count() > 0) {
                $res[] = $v;
            }
        }
        return $res;
    }

    public static function getVehiclesForCompany($companyID) {
        $listOfVehicles = self::where('company_id', '=', $companyID)
                            ->where('deleted_at', '=', NULL)
                            ->get();

        return $listOfVehicles;
    }

}

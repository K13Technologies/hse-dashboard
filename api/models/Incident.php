<?php

class Incident extends Eloquent {

    protected $table = 'incidents';
    protected $primaryKey = 'incident_id';
    public $timestamps = false;
    public $fillable = array('ts', 'created_at', 'worker_id');
    private static $properties = array('title', 'lsd', 'utm', 'source_receiver_line', 'latitude',
        'longitude', 'location', 'specific_area', 'road', 'description',
        'immediate_action', 'corrective_action', 'corrective_action_applied',
        'corrective_action_implementation','root_cause');
    public static $formTypeName = 'Incident';

    public static function formTypeName () {
        return self::$formTypeName;
    }
    /**
     * Returns the incident
     * @param int $incidentID
     * @return Incident returns an incident if exists or NULL if it doesn't;
     */
    public static function getByID($incidentID) {
        $result = Incident::where('incident_id', '=', $incidentID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the incident
     * @param int $incidentID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($incidentID) {  
        $result = self::where('incident_id', '=', $incidentID)
                        ->join('workers', 'workers.worker_id', '=', 'incidents.worker_id')
                        ->select('workers.company_id')
                        ->get();

        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public function getProperties() {
        return self::$properties;
    }

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function mvds() {
        return $this->hasMany('IncidentMVD', 'incident_id');
    }

    public function treatments() {
        return $this->hasMany('IncidentTreatment', 'incident_id');
    }

    public function releaseSpill() {
        return $this->hasOne('IncidentReleaseSpill', 'incident_id');
    }

    public function persons() {
        return $this->hasMany('IncidentPerson', 'incident_id')->orderBy('type', 'asc');
    }
    
    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }
    
    public function incidentTypes() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('IncidentType', 'incident_has_types', 'incident_id', 'incident_type_id');
    }
    public function hasType(IncidentType $type){
        foreach ($this->incidentTypes as $t){
            if ($t->incident_type_id == $type->incident_type_id){
                return true;
            }
        }
        return false;
    }
    
    public function addType(IncidentType $type){
        $this->incidentTypes()->save($type);
    }

    public function removeType($type){
        $this->incidentTypes()->detach($type);
    }
    
    

    public function incidentTypeIds() {
        return $this->incidentTypes()->getRelatedIds();
//        ::where(''HazardChecklistItem', 'flha_has_hazard_items', 'flha_id', 'flha_hazard_item_id'); 
    }

    public function incidentActivities() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('Activity', 'incident_has_activities', 'incident_id', 'activity_id');
    }

    public function incidentActivityIds() {
        return $this->incidentActivities()->getRelatedIds();
//        ::where(''HazardChecklistItem', 'flha_has_hazard_items', 'flha_id', 'flha_hazard_item_id'); 
    }

    public function shouldHaveMVD() {
        if (count(array_intersect($this->incidentTypeIds(), IncidentType::$mvd)) > 0) {
            return TRUE;
        }
        return false;
    }

    public function hasMVD(IncidentType $incidentType) {
        return $this->mvds()->where('incident_type_id','=',$incidentType->incident_type_id)->get()->first();
    }

    public function shouldHaveTreatment() {
        if (count(array_intersect($this->incidentTypeIds(), IncidentType::$medical)) > 0) {
            return TRUE;
        }
        return false;
    }

    public function hasTreatment(IncidentType $incidentType) {
        return $this->treatments()->where('incident_type_id','=',$incidentType->incident_type_id)->get()->first();
    }

    public function shouldHaveReleaseSpill() {
        if (count(array_intersect($this->incidentTypeIds(), IncidentType::$release_and_spill)) > 0) {
            return TRUE;
        }
        return false;
    }

    public function hasReleaseSpill() {
        return $this->releaseSpill instanceof IncidentReleaseSpill;
    }

    public function statements() {
        return $this->hasMany('IncidentPartStatement', 'incident_id');
    }

    public function setFields(array $input) {
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)) {
                $this->$key = trim($val);
            }
        }
        return $this;
    }

    public static function getWithFullDetails($incidentId) {
        $incident = Incident::with('persons', 
                'mvds', 
                'treatments', 'releaseSpill')->find($incidentId);
        
        foreach ($incident->mvds as $mvd){
            $mvd->getWithDetails();
        }
        foreach ($incident->treatments as $treatment){
            $treatment->getWithDetails();
        }
        
        $incident->incidentTypeIds = $incident->incidentTypeIds();
        $incident->incidentActivityIds = $incident->incidentActivityIds();
        $incident->photoIds = $incident->extractPhotoIds();
        return $incident;
    }

    public function setTypes(array $input) {
        $validatedList = array();
        $incidentTypes = $input['incidentTypeIds'];

        foreach ($incidentTypes as $incidentTypeId) {
            if (IncidentType::find($incidentTypeId)) {
                $validatedList[] = $incidentTypeId;
            }
        }
        $this->incidentTypes()->sync($validatedList);
        return true;
    }

    public function setActivities(array $input) {
        $validatedList = array();
        $incidentActivities = $input['incidentActivityIds'];

        foreach ($incidentActivities as $incidentActivityId) {
            if (Activity::find($incidentActivityId)) {
                $validatedList[] = $incidentActivityId;
            }
        }
        $this->incidentActivities()->sync($validatedList);
        return true;
    }

    public static function getRecentIncidentsForWorker($workerId) {
//        $hazard  
        $listOfIncidents = self::where('worker_id', '=', $workerId)->whereRaw(ApiController::$interval)->get();
        return $listOfIncidents;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('incidentActivities', 'incidentTypes', 'addedBy.group.businessUnit.division.company')->orderBy('ts', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('incidentActivities', 'incidentTypes', 'addedBy.group.businessUnit.division.company')
                    ->where('deleted_at', '=', NULL)
                    ->orderBy('ts', 'desc')
                    ->whereIn('worker_id', $companyWorkers)
                    ->get()->all();
        }
    }

    public static function getRecentIncidentsForCompany(Admin $admin, $amount) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::with('incidentActivities', 'incidentTypes', 'addedBy.group.businessUnit.division.company')
                ->where('deleted_at', '=', NULL)
                ->orderBy('ts', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->take($amount)
                ->get();
    }

    // $month parameter format is YYYY-MM
    public static function getForCompanyByMonth($admin, $month) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::with('incidentActivities', 'incidentTypes', 'addedBy.group.businessUnit.division.company')
                ->where('deleted_at', '=', NULL)
                ->where('created_at', '>=', $month . '-01 00:00:00')
                ->where('created_at', '<=', $month . '-31 23:59:59')
                ->orderBy('ts', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->get()
                ->all();
    }

    public static function getNumberOfIncidentsForCurrentYear(Admin $admin) {
        $currentYear = date("Y") . '-00-00 00:00:00';

        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return 0;
        }
        return self::with('addedBy.group.businessUnit.division.company')
                    ->where('created_at', '>=', $currentYear)
                    ->where('deleted_at', '=', NULL)
                    ->whereIn('worker_id', $companyWorkers)
                    ->get()
                    ->count();
    }

    public function getDates() {
        return array();
    }
    
    public function extractPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        unset($this->photos);
        return $ids;
    }
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "incidents.worker_id")
              ->where('workers.group_id','=', $groupId);
        if ($tsStart){
            $query->whereBetween('ts',array($tsStart,$tsEnd));
        }else{
            $query->where('ts','<',$tsEnd);
        }
        $total = $query->get()->count();
        $totalActionRequired = $query->where('corrective_action_applied','=',0)->get()->count();
        $totalActionRequiredAndFixed = $query->whereNotNull('completed_on')->get()->count();
        
        return ['total'=>$total,
                'areq' =>$totalActionRequired,
                'afix' =>$totalActionRequiredAndFixed];
    }
}

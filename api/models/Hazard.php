<?php

class Hazard extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hazards';
    protected $primaryKey = 'hazard_id';
    public $timestamps = false;
    public $fillable = array('ts', 'created_at', 'worker_id');
    private static $properties = array('title', 'site', 'specific_location', 'lsd', 'wellpad', 'road', 'description',
        'corrective_action', 'corrective_action_applied', 'corrective_action_implementation', 'action', 'completed_on',
        'comment', 'hazard_activity_id');
    public static $formTypeName = 'Hazard Card';

    /**
     * Returns the hazard
     * @param int $hazardID
     * @return Hazard returns a hazard card if exists and NULL if it doesn't;
     */
    public static function getByID($hazardID) {
        $result = Hazard::where('hazard_id', '=', $hazardID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the hazard card
     * @param int $hazardID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($hazardID) {  
        $result = self::where('hazard_id', '=', $hazardID)
                        ->join('workers', 'workers.worker_id', '=', 'hazards.worker_id')
                        ->select('workers.company_id')
                        ->get();

        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    // Added as a standardized name for future use and so as to not break any other code.
    public function added_by() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function hazardActivity() {
        return $this->belongsTo('HazardActivity', 'hazard_activity_id');
    }

    /*
     * maps the hazard - categories relationship
     */

    public function hazardCategories() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('HazardCategory', 'hazard_has_hazard_category', 'hazard_id', 'hazard_category_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }
    
    
    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
                if (($key == 'corrective_action_applied' && $input[$key] == 1) OR
                        $key == 'corrective_action' && $this->corrective_action != $input[$key]) {
                    $this->completed_on = NULL;
                    $this->action = '';
                }
            }
        }

        return $this;
    }

    // This is used by the dashboard admins when saving edits to a hazard card
    public function setWebFormFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }

        /*Empty checkboxes are not sent in POST data and so must be handled differently*/
        if (isset($input['corrective_action_applied'])) {
            // Make sure all fields are reset
            $this->corrective_action_applied = 1;
            $this->corrective_action_implementation = '';
        } else {
            $this->corrective_action_applied = 0;
        }

        //If the completed on field is not set, it will set to a value of 0000-00-00 instead of NULL as it should be 
        // This addresses that issue
        if($input['completed_on']=='') {
            $this->completed_on = NULL;
        }
 
        $this->hazard_activity_id = $input['hazard_activity_id'];

        return $this;
    }

    public function setHazardCategories(array $input) {
        $validatedList = array();
        $hazardCategories = $input['hazard_category_ids'];
        foreach ($hazardCategories as $hazardCategoryId) {
            if (HazardCategory::find($hazardCategoryId)) {
                $validatedList[] = $hazardCategoryId;
            }
        }
        if (empty($validatedList)) {
            return false;
        }
        $this->hazardCategories()->sync($validatedList);
        return true;
    }

    public static function getRecentHazardsForWorker($workerId) {
        $listOfHazards = self::with(array('hazardCategories', 'photos'))
                            ->where('worker_id', '=', $workerId)
                            ->where('deleted_at', '=', NULL)
                            ->whereRaw(ApiController::$interval)->get(); // This line needs more explanation...
        
        $arrayList = array();
        foreach ($listOfHazards as $l) {

            $l->hazardCategoryIdList = $l->extractHazardCategoryIds();
            $l->hazardPhotoIdList = $l->extractHazardPhotoIds();

            $elem = $l->toArray();
            unset($elem['hazard_categories']);
            unset($elem['photos']);
            $arrayList[] = $elem;
        }
        $result = \Illuminate\Support\Collection::make($arrayList);
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;
    }

    private function extractHazardCategoryIds() {
        $ids = array();
        foreach ($this->hazard_categories as $c) {
            $ids[] = $c->hazard_category_id;
        }
        return $ids;
    }

    private function extractHazardPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        return $ids;
    }

    public static function getAllHazardsNotCorrected(Admin $admin) {
        $companyGroups = Group::getAllForCompany($admin->company_id);
        if (empty($companyGroups)) {
            return array();
        }
        $hazards = self::where('corrective_action_applied', '=', 0)
                        ->where('completed_on', '=', NULL)
                        ->select('hazards.*')
                        ->join('workers', 'workers.worker_id', '=', 'hazards.worker_id')
                        ->whereIn('workers.group_id', $companyGroups)
                        ->orderBy('created_at', 'desc')->get()->all();

        return $hazards;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('hazardCategories', 'hazardActivity', 'addedBy.group.businessUnit.division.company')->orderBy('ts', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('hazardCategories', 'hazardActivity', 'addedBy.group.businessUnit.division.company')
                        ->where('deleted_at', '=', NULL)
                        ->orderBy('ts', 'desc')
                        ->whereIn('worker_id', $companyWorkers)
                        ->get()->all();
        }
    }

    public static function getRecentHazardsForCompany($admin, $amount) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }

        return self::with('hazardCategories', 'hazardActivity', 'addedBy.group.businessUnit.division.company')
                   ->where('deleted_at', '=', NULL)
                   ->orderBy('ts', 'DESC')
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
        return self::with('hazardCategories', 'hazardActivity', 'addedBy.group.businessUnit.division.company')
                   ->where('deleted_at', '=', NULL)
                   ->where('created_at', '>=', $month . '-01 00:00:00')
                   ->where('created_at', '<=', $month . '-31 23:59:59')
                   ->orderBy('ts', 'DESC')
                   ->whereIn('worker_id', $companyWorkers)
                   ->get()
                   ->all();
    }

    public function getDates() {
        return array();
    }
    
    public static function formTypeName () {
        return self::$formTypeName;
    }
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "hazards.worker_id")
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

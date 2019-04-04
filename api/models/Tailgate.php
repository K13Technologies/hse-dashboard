<?php

class Tailgate extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tailgates';
    protected $primaryKey = 'tailgate_id';
    public $timestamps = false;
    protected $hidden = array('hazardChecklistItems');
    public $fillable = array('ts', 'created_at', 'worker_id');

    CONST ASSESSMENT_TYPE_HAZARD = 1;
    CONST ASSESSMENT_TYPE_PRE_JOB = 2;
    CONST ASSESSMENT_TYPE_TAILGATE = 3;
    CONST ASSESSMENT_TYPE_SAFETY = 4;

    private static $properties = array('title', 'job_description', 'stars_site', 'phone_number', 'job_number',
        'assessment_type', 'comment', 'fit_for_duty', 'fit_for_duty_description',
        'proper_training', 'proper_training_description', 'job_scope_and_procedures',
        'job_scope_and_procedures_description', 'hazards_identified',
        'hazards_identified_description', 'controls_implemented',
        'controls_implemented_description'
    );
    private static $ha_properties = array('fit_for_duty', 'fit_for_duty_description',
        'proper_training', 'proper_training_description', 'job_scope_and_procedures',
        'job_scope_and_procedures_description', 'hazards_identified',
        'hazards_identified_description', 'controls_implemented',
        'controls_implemented_description'
    );

    public static $formTypeName = 'Tailgate';

    public static function formTypeName () {
        return self::$formTypeName;
    }

    /**
     * Returns the tailgate
     * @param int $tailgateID
     * @return Tailgate returns a tailgate if exists or NULL if it doesn't;
     */
    public static function getByID($tailgateID) {
        $result = Tailgate::where('tailgate_id', '=', $tailgateID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the tailgate
     * @param int $tailgateID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($tailgateID) {  
        $result = self::where('tailgate_id', '=', $tailgateID)
                        ->join('workers', 'workers.worker_id', '=', 'tailgates.worker_id')
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

    public function locations() {
        return $this->hasMany('TailgateLocation', 'tailgate_id');
    }

    public function permits() {
        return $this->hasMany('TailgatePermit', 'tailgate_id');
    }

    public function supervisors() {
        return $this->hasMany('TailgateSupervisor', 'tailgate_id');
    }

    public function lsds() {
        return $this->hasMany('TailgateLSD', 'tailgate_id');
    }

    public function tasks() {
        return $this->hasMany('TailgateTask', 'tailgate_id');
    }

    public function signoffVisitors() {
        return $this->hasMany('TailgateSignoffVisitor', 'tailgate_id');
    }

    public function signoffWorkers() {
        return $this->hasMany('TailgateSignoffWorker', 'tailgate_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function completion() {
        return $this->morphOne('JobCompletion', 'completioner');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }

    public function hazardChecklistItems() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('HazardChecklistItem', 'tailgate_has_hazard_items', 'tailgate_id', 'flha_hazard_item_id');
    }

    public function hazardChecklistItemIds() {
        return $this->hazardChecklistItems()->getRelatedIds();
//        ::where(''HazardChecklistItem', 'flha_has_hazard_items', 'flha_id', 'flha_hazard_item_id'); 
    }

    public function taskList() {
        $result = array();
        foreach ($this->tasks as $t) {
            $hazardList = array();
            foreach ($t->hazards as $h) {
                $hazardList[] = $h->tailgate_task_hazard_id;
            }
            $t->hazardList = $hazardList;
            unset($t->hazards);
            $result[$t->tailgate_task_hazard_id] = $t->toArray();
        }
        return $result;
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function setHAFields(array $input) {
        foreach (self::$ha_properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function setLocations(array $input) {
        $locations = $input['locations'];
        $this->locations->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($locations as $location) {
            $l = new TailgateLocation();
            $location = trim($location);
            if ($location != "") {
                $l->location = $location;
                $all[] = $l;
            } else {
                unset($l);
            }
        }
        $this->locations()->saveMany($all);
        return true;
    }

    public function deleteAllLocations () {
        $this->locations->each(function($l) {
            $l->delete();
        });
    }

    public function deleteAllLSDs () {
        $this->lsds->each(function($l) {
            $l->delete();
        });
    }

    public function setPermits(array $input) {
        $permits = $input['permits'];
        $this->permits->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($permits as $permit) {
            $p = new TailgatePermit();
            $permit = trim($permit);
            if ($permit != "") {
                $p->permit_number = $permit;
                $all[] = $p;
            } else {
                unset($p);
            }
        }
        $this->permits()->saveMany($all);
        return true;
    }

    public function setLSDs(array $input) {
        $lsds = $input['lsds'];
        $this->lsds->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($lsds as $lsd) {
            $l = new TailgateLSD();
            $lsd = trim($lsd);
            if ($lsd != "") {
                $l->lsd = $lsd;
                $all[] = $l;
            } else {
                unset($l);
            }
        }
        $this->lsds()->saveMany($all);
        return true;
    }

    public function setSupervisors(array $input) {
        $supervisors = $input['supervisors'];
        $this->supervisors->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($supervisors as $supervisor) {
            $s = new TailgateSupervisor();
            $supervisor = trim($supervisor);
            if ($supervisor != "") {
                $s->name = $supervisor;
                $all[] = $s;
            } else {
                unset($s);
            }
        }
        $this->supervisors()->saveMany($all);
        return true;
    }

    // Copypasta probably needs cleaning....
    public function setChecklist(array $input) {
        $validatedList = array();
        $flhaHazardItems = $input['hazardChecklistItemIds'];

        foreach ($flhaHazardItems as $flhaHazardItemId) {
            if (HazardChecklistItem::find($flhaHazardItemId)) {
                $validatedList[] = $flhaHazardItemId;
            }
        }
        $this->hazardChecklistItems()->sync($validatedList);
        return true;
    }

    // Copypasta probably needs cleaning.... Also not in use --  see  getCompletedAndInProgressListsForWorker
    public static function getRecentFLHAForWorker($workerId) {
        $listOfPOs = self::with(array('positiveObservationCategories', 'photos', 'personsObserved'))
                        ->where('worker_id', '=', $workerId)
                        ->where('deleted_at', '=', NULL)
                        ->whereRaw(ApiController::$interval)->get();
        $arrayList = array();
        foreach ($listOfPOs as $p) {

            $p->categoryIds = $p->extractPositiveObservationCategoryIds();
            $p->personsObserved = $p->extractPersonsObservedList();
            $p->photoIds = $p->extractPhotoIds();

            $elem = $p->toArray();
            unset($elem['positive_observation_categories']);
            unset($elem['persons_observed']);
            unset($elem['photos']);
            $arrayList[] = $elem;
        }
        $result = \Illuminate\Support\Collection::make($arrayList);
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;
    }

    private function extractPositiveObservationCategoryIds() {
        $ids = array();
        foreach ($this->positiveObservationCategories as $c) {
            $ids[] = $c->positive_observation_category_id;
        }
        return $ids;
    }

    private function extractPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        return $ids;
    }

    private function extractPersonsObservedList() {
        $personsList = array();
        foreach ($this->persons_observed as $p) {
            $person = new stdClass();
            $person->name = $p->name;
            $person->company = $p->company;
            $personsList[] = $person;
        }
        return $personsList;
    }

    // Returns tailgates which the user recently created, not indcluding DELETED items
    public static function getCompletedAndInProgressListsForWorker(Worker $worker) {
        $latest = self::with('completion')
                    ->where('worker_id', '=', $worker->worker_id)
                    ->where('deleted_at', '=', NULL)
                    ->whereRaw(ApiController::$interval)->get();
        $completed = array();
        $inProgress = array();
        foreach ($latest as $tailgate) {
            if ($tailgate->completion instanceof JobCompletion) {
                $completed[] = $tailgate->toArray();
            } else {
                $inProgress[] = $tailgate->toArray();
            }
        }
        unset($latest);

        $result['completed'] = $completed;
        $result['in_progress'] = $inProgress;

        return $result;
    }

    // Copypasta which will probbaly need cleaning
    public static function getIncompleteFLHAList(Admin $admin) {
        $companyGroups = Group::getAllForCompany($admin->company_id);
        if (empty($companyGroups)) {
            return array();
        }
        $flhas = self::has('completion', '=', 0)
                        ->select('flhas.*')
                        ->join('workers', 'workers.worker_id', '=', 'flhas.worker_id')
                        ->whereRaw('created_at < DATE_SUB(NOW(), INTERVAL 720 MINUTE)')
                        ->whereIn('workers.group_id', $companyGroups)->get()->all();

        return $flhas;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('completion')->orderBy('ts', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('completion')
                    ->where('deleted_at', '=', NULL)
                    ->orderBy('ts', 'desc')
                    ->whereIn('worker_id', $companyWorkers)
                    ->get()->all();
        }
    }

    public static function getRecentTailgatesForCompany(Admin $admin, $amount) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::with('completion')
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
        return self::with('completion')
                ->where('deleted_at', '=', NULL)
                ->where('created_at', '>=', $month . '-01 00:00:00')
                ->where('created_at', '<=', $month . '-31 23:59:59')
                ->orderBy('ts', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->get()
                ->all();
    }

    public static function getWithFullDetails($tailgateId) {
        $tailgate = self::with('completion', 'hazardChecklistItems', 'tasks', 'signoffVisitors', 'signoffWorkers')->find($tailgateId);
//        $tailgate = self::with('completion','hazardChecklistItems')->find($tailgateId);
        $tailgate->hazardChecklistItemIds = $tailgate->hazardChecklistItemIds();
        $tailgate->tasks = $tailgate->taskList();
        $tailgate->locations = array_pluck($tailgate->locations()->get()->toArray(), 'location');
        $tailgate->permits = array_pluck($tailgate->permits()->get()->toArray(), 'permit_number');
        $tailgate->supervisors = array_pluck($tailgate->supervisors()->get()->toArray(), 'name');
        $tailgate->lsds = array_pluck($tailgate->lsds()->get()->toArray(), 'lsd');
//        $tailgate->photoIds = $tailgate->extractPhotoIds();

        return $tailgate;
    }

    public static function getCompleteWithFullDetails($tailgateId) {
        $tailgate = self::with('completion', 'hazardChecklistItems.hazardChecklistCategory', 'tasks.hazards', 'signoffVisitors', 'signoffWorkers')->find($tailgateId);
        $checklist = array();
        foreach ($tailgate->hazardChecklistItems as $hci) {
            $checklist[$hci->flha_hazard_category_id][] = $hci;
        }
        $tailgate->checklist = $checklist;
        return $tailgate;
    }

    public function getTypeOfAssessment() {
        switch ($this->assessment_type) {
            case self::ASSESSMENT_TYPE_HAZARD:
                return "Hazard Assessment";
            case self::ASSESSMENT_TYPE_PRE_JOB:
                return "Pre-Job";
            case self::ASSESSMENT_TYPE_TAILGATE:
                return "Tailgate Meeting";
            case self::ASSESSMENT_TYPE_SAFETY:
                return "Safety Meeting";

            default:
                break;
        }
    }

    public function getDates() {
        return array();
    }
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "tailgates.worker_id")
              ->where('workers.group_id','=', $groupId);
        if ($tsStart){
            $query->whereBetween('ts',array($tsStart,$tsEnd));
        }else{
            $query->where('ts','<',$tsEnd);
        }
        $total = $query->get()->count();
        $areqQuery = clone $query;
        $totalActionRequired = $areqQuery->has('completion', '=', 0)->get()->count();
        $totalActionRequiredAndFixed = $query->has('completion', '=', 1)->get()->count();
        
        return ['total'=>$total,
                'areq' =>$totalActionRequired,
                'afix' =>$totalActionRequiredAndFixed];
    }
}

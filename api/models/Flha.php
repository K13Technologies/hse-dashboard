<?php

class Flha extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flhas';
    protected $primaryKey = 'flha_id';
    public $timestamps = false;
    protected $softDelete = true;
    protected $hidden = array('hazardChecklistItems');
    public $fillable = array('ts', 'created_at', 'worker_id');
    private static $properties = array('title', 'site', 'location', 'specific_location',
        'client', 'muster_point', 'supervisor_number', 'safety_rep_number', 'safety_rep_name', 'radio_channel',
        'supervisor_name', 'job_description', 'gloves_removed', 'gloves_removed_description',
        'working_alone', 'working_alone_description', 'warning_ribbon', 'warning_ribbon_description');
    public static $formTypeName = 'FLHA';

    public static function formTypeName () {
        return self::$formTypeName;
    }

    /**
     * Returns the flha
     * @param int $flhaID
     * @return Flha returns an flha if exists or NULL if it doesn't;
     */
    public static function getByID($flhaID) {
        $result = Flha::where('flha_id', '=', $flhaID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the flha
     * @param int $flhaID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($flhaID) {  
        $result = self::where('flha_id', '=', $flhaID)
                        ->join('workers', 'workers.worker_id', '=', 'flhas.worker_id')
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

    public function tasks() {
        return $this->hasMany('FlhaTask', 'flha_id');
    }

    public function spotchecks() {
        return $this->hasMany('Spotcheck', 'flha_id');
    }

    public function signoffVisitors() {
        return $this->hasMany('SignoffVisitor', 'flha_id');
    }

    public function signoffWorkers() {
        return $this->hasMany('SignoffWorker', 'flha_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function completion() {
        return $this->morphOne('JobCompletion', 'completioner');
    }
    
     public function locations() {
        return $this->hasMany('FlhaLocation', 'flha_id');
    }

    public function permits() {
        return $this->hasMany('FlhaPermit', 'flha_id');
    }

    public function sites() {
        return $this->hasMany('FlhaSite', 'flha_id');
    }

    public function lsds() {
        return $this->hasMany('FlhaLSD', 'flha_id');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }

    public function hazardChecklistItems() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('HazardChecklistItem', 'flha_has_hazard_items', 'flha_id', 'flha_hazard_item_id');
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
                $hazardList[] = $h->flha_task_hazard_id;
            }
            $t->hazardList = $hazardList;
            unset($t->hazards);
            $result[$t->flha_task_id] = $t->toArray();
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

    public function setPersonsObserved(array $input) {
        $validatedList = array();
        $observedList = $input['personsObserved'];
        $this->personsObserved->each(function($p) {
            $p->delete();
        });
        foreach ($observedList as $observed) {
            $person = new PersonObserved();

            $hasAllFields = $person->setFields($observed);
            if ($hasAllFields) {
                $this->personsObserved()->save($person);
                $validatedList[] = $person;
            }
        }
        if (empty($validatedList)) {
            return false;
        }
        return true;
    }

    // This appears to be UNUSED --  see   getCompletedAndInProgressListsForWorker
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

    // Returns a list of recently created items by the worker (not including deleted items)
    public static function getCompletedAndInProgressListsForWorker(Worker $worker) {
        $latest = self::with('completion')
                    ->where('worker_id', '=', $worker->worker_id)
                    ->where('deleted_at', '=', NULL)
                    ->whereRaw(ApiController::$interval)->get();
        $completed = array();
        $inProgress = array();
        foreach ($latest as $flha) {
            if ($flha->completion instanceof JobCompletion) {
                $completed[] = $flha->toArray();
            } else {
                $inProgress[] = $flha->toArray();
            }
        }
        unset($latest);

        $result['completed'] = $completed;
        $result['in_progress'] = $inProgress;

        return $result;
    }

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

    public static function getRecentFlhasForCompany(Admin $admin, $amount) {
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

    public static function getWithFullDetails($flhaId) {
        $flha = self::with('completion', 'spotchecks', 'hazardChecklistItems', 'tasks', 'signoffVisitors', 'signoffWorkers.breaks')->find($flhaId);
        $flha->hazardChecklistItemIds = $flha->hazardChecklistItemIds();
        $flha->tasks = $flha->taskList();
        
        $flha->locations = array_pluck($flha->locations()->get()->toArray(), 'location');
        $flha->permits = array_pluck($flha->permits()->get()->toArray(), 'permit_number');
        $flha->sites = array_pluck($flha->sites()->get()->toArray(), 'site');
        $flha->lsds = array_pluck($flha->lsds()->get()->toArray(), 'lsd');
        
        $flha->photoIds = $flha->extractPhotoIds();
        unset($flha->photos);

        return $flha;
//        unset($result['hazard_checklist_items']);
    }

    public static function getCompleteWithFullDetails($flhaId) {
        $flha = self::with('completion', 'spotchecks', 'hazardChecklistItems.hazardChecklistCategory', 'tasks.hazards', 'signoffVisitors', 'signoffWorkers.breaks')->find($flhaId);
        $checklist = array();
        foreach ($flha->hazardChecklistItems as $hci) {
            $checklist[$hci->flha_hazard_category_id][] = $hci;
        }
        $flha->checklist = $checklist;
        return $flha;
    }

    public function setLocations(array $input) {
        $locations = $input['locations'];
        $this->locations->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($locations as $location) {
            $l = new FlhaLocation();
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

    // Handles the setting of locations from the editable webapp form
    // This must be handled slightly differently than setLocations because the input data is structured differently
    // In that it is more complex
    public function setWebLocations(array $locations) {
        $this->locations->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($locations as $location) {
            $l = new FlhaLocation();
            $location = trim($location['location']);
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
    
    public function setPermits(array $input) {
        $permits = $input['permits'];
        $this->permits->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($permits as $permit) {
            $p = new FlhaPermit();
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

    public function setWebPermits(array $input) {
        $permits = $input;
        $this->permits->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($permits as $permit) {
            $p = new FlhaPermit();
            $permit = trim($permit['permit_number']);
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
            $l = new FlhaLSD();
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

    public function setWebLSDs(array $input) {
        $lsds = $input;
        $this->lsds->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($lsds as $lsd) {
            $l = new FlhaLSD();
            $lsd = trim($lsd['lsd']);
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
    
    public function setSites(array $input) {
        $sites = $input['sites'];
        $this->sites->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($sites as $site) {
            $l = new FlhaSite();
            $site = trim($site);
            if ($site != "") {
                $l->site = $site;
                $all[] = $l;
            } else {
                unset($l);
            }
        }
        $this->sites()->saveMany($all);
        return true;
    }

    public function setWebFormSites(array $input) {
        $sites = $input;
        $this->sites->each(function($l) {
            $l->delete();
        });
        $all = array();
        foreach ($sites as $site) {
            $l = new FlhaSite();
            $site = trim($site['site']);
            if ($site != "") {
                $l->site = $site;
                $all[] = $l;
            } else {
                unset($l);
            }
        }
        $this->sites()->saveMany($all);
        return true;
    }
    
    public function getDates() {
        return array();
    }

    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "flhas.worker_id")
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

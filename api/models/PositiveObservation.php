<?php

class PositiveObservation extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'positive_observations';
    protected $primaryKey = 'positive_observation_id';
    public $timestamps = false;
    public $fillable = array('ts', 'created_at', 'worker_id');
    private static $properties = array('title', 'site', 'specific_location', 'lsd', 'wellpad', 'road',
        'task_1_title', 'task_1_description', 'task_2_title', 'task_2_description',
        'task_3_title', 'task_3_description', 'description', 'is_positive_observation',
        'is_po_details', 'correct_on_site', 'comment');
    public static $formTypeName = 'Field Observation';

    public static function formTypeName () {
        return self::$formTypeName;
    }

    /**
     * Returns the observation
     * @param int $observationID
     * @return PositiveObservation returns if exists or NULL if it doesn't;
     */
    public static function getByID($observationID) {
        $result = PositiveObservation::where('positive_observation_id', '=', $observationID)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the company ID of the observation
     * @param int $observationID
     * @return int returns a company ID or NULL if unable to
     */
    public static function getCompanyID($observationID) {  
        $result = self::where('positive_observation_id', '=', $observationID)
                        ->join('workers', 'workers.worker_id', '=', 'positive_observations.worker_id')
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

    public function activity() {
        return $this->belongsTo('PositiveObservationActivity', 'positive_observation_activity_id');
    }

    /*
     * maps the po - categories relationship
     */

    public function positiveObservationCategories() {
//        return $this->belongsToMany('ENTITY', 'PIVOT TABLE', 'THIS->ID', 'ENTITY->ID'); 
        return $this->belongsToMany('PositiveObservationCategory', 'po_has_po_category', 'po_id', 'po_category_id');
    }

    /*
     * maps the po - people observed relationship
     */

    public function personsObserved() {
        return $this->hasMany('PersonObserved', 'positive_observation_id');
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
                if ($key == 'is_positive_observation' && $input[$key] == 1) {
                    $this->completed_on = NULL;
                    $this->action = '';
                }
            }
        }
        $this->positive_observation_activity_id = $input['positive_observation_activity_id'];

        return $this;
    }

    public function setWebFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }

        // Deleting pre-existing tasks
        if(!isset($input['task_2_title'])) {
            $this->task_2_title = '';
            $this->task_2_description = '';
        }
        if(!isset($input['task_3_title'])) {
            $this->task_3_title = '';
            $this->task_3_description = '';
        }

        // Manually setting because it doesn't seem to work otherwise
        $this->positive_observation_activity_id = $input['positive_observation_activity_id'];
        $this->action = $input['action'];

        if($input['completed_on']=='') {
            //If the completed on field is not set, it will set to a value of 0000-00-00 instead of NULL as it should be 
            // This addresses that issue
            $this->completed_on = NULL;
        } else {
            $this->completed_on = $input['completed_on'];
        }

        // CHECKBOXES
        /*Empty checkboxes are not sent in POST data and so must be handled differently*/
        if (isset($input['is_positive_observation'])) {
            $this->is_positive_observation = 1;
            $this->correct_on_site = 0; //Field not relevant when it is positive --  set to default
        } else {
            $this->is_positive_observation = 0;
            if (isset($input['correct_on_site'])) {
                $this->correct_on_site = 1;
            } else {
                $this->correct_on_site = 0;
            }
        }

        return $this;
    }

    public function setWebPersonsObserved(array $input) {
        $validatedList = array();
        $observedList = $input['personsObserved'];

        // Delete all previous people
        $this->personsObserved->each(function($p) {
            $p->delete();
        });

        // Create all new people 
        foreach ($observedList as $observed) {

            // Get the observation ID which was included as a hidden field in the form and set all objects to have that ID
            $observed['positive_observation_id'] = $input['positive_observation_id'];
            
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

    public function setCategories(array $input) {
        $validatedList = array();
        $positiveObservationCategories = $input['categoryIds'];
        foreach ($positiveObservationCategories as $categoryId) {
            if (PositiveObservationCategory::find($categoryId)) {
                $validatedList[] = $categoryId;
            }
        }
        if (empty($validatedList)) {
            return false;
        }
        $this->positiveObservationCategories()->sync($validatedList);
        return true;
    }

    public function setPersonsObserved(array $input) {
        $validatedList = array();
        $observedList = $input['personsObserved'];

        // Delete all previous people
        $this->personsObserved->each(function($p) {
            $p->delete();
        });

        // Create all new people 
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

    public static function getRecentObservationsForCompany($admin, $amount) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::with('positiveObservationCategories', 'personsObserved', 'activity', 'addedBy.group.businessUnit.division.company')
                        ->where('deleted_at', '=', NULL)
                        ->whereIn('worker_id', $companyWorkers)
                        ->orderBy('ts', 'DESC')
                        ->take($amount)
                        ->get();
    }

    // $month parameter format is YYYY-MM
    public static function getForCompanyByMonth($admin, $month) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::with('positiveObservationCategories', 'personsObserved', 'activity', 'addedBy.group.businessUnit.division.company')
                        ->where('deleted_at', '=', NULL)
                        ->where('created_at', '>=', $month . '-01 00:00:00')
                        ->where('created_at', '<=', $month . '-31 23:59:59')
                        ->whereIn('worker_id', $companyWorkers)
                        ->orderBy('ts', 'DESC')
                        ->get()
                        ->all();
    }

    public static function getRecentPOForWorker($workerId) {
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

    public static function getAllPONotCorrected(Admin $admin) {
        $companyGroups = Group::getAllForCompany($admin->company_id);
        if (empty($companyGroups)) {
            return array();
        }
        $pos = self::where('is_positive_observation', '=', 0)
                        ->where('correct_on_site', '=', 0)
                        ->where('completed_on', '=', NULL)
                        ->select('positive_observations.*')
                        ->join('workers', 'workers.worker_id', '=', 'positive_observations.worker_id')
                        ->whereIn('workers.group_id', $companyGroups)
                        ->orderBy('created_at', 'desc')->get()->all();

        return $pos;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('positiveObservationCategories', 'personsObserved', 'activity', 'addedBy.group.businessUnit.division.company')
                            ->orderBy('created_at', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('positiveObservationCategories', 'personsObserved', 'activity', 'addedBy.group.businessUnit.division.company')
                            ->where('deleted_at', '=', NULL)
                            ->whereIn('worker_id', $companyWorkers)
                            ->orderBy('created_at', 'desc')->get()->all();
        }
    }

    public function getDates() {
        return array();
    }
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "positive_observations.worker_id")
              ->where('workers.group_id','=', $groupId);
        if ($tsStart){
            $query->whereBetween('ts',array($tsStart,$tsEnd));
        }else{
            $query->where('ts','<',$tsEnd);
        }
        $total = $query->get()->count();
        $totalActionRequired = $query->where('is_positive_observation','=',0)->get()->count();
        $totalActionRequiredAndFixed = $query->whereNotNull('completed_on')->get()->count();
        
        return ['total'=>$total,
                'areq' =>$totalActionRequired,
                'afix' =>$totalActionRequiredAndFixed];
    }
}

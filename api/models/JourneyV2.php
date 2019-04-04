<?php

class JourneyV2 extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journeys_v2';
    protected $primaryKey = 'journey_id';
    public $timestamps = false;
    private static $properties = array('started_at', 'created_at', 'starting_point');
    public static $formTypeName = 'Journey';

    public static function formTypeName () {
        return self::$formTypeName;
    }

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    // Standardized named for use when comparing to other forms
    public function added_by() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function journeyFrom() {
        return $this->belongsTo('JourneyLocation', 'starting_point');
    }

    public function endpoints() {
        return $this->hasMany('JourneyEndpoint', 'journey_id');
    }

    public function checkins() {
        return $this->hasMany('JourneyCheckinV2', 'journey_id');
    }

    public function setFields(array $input, Worker $addedBy = NULL) {

        if ($addedBy instanceof Worker) {
            $this->worker_id = $addedBy->worker_id;
        }

        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }

        return $this;
    }
    
    public function terminatedIncorrectly(){
        $endpoints= $this->separateEndpoints();
        $unarrived = $endpoints[1];
        if ($this->finished_at && count($unarrived)){
            return true;
        }
        return false;
    }
    
    public function separateEndpoints(){
        $currentEndpoints = $this->endpoints;
        $arrivedEndpoints= array();
        $unarrivedEndpoints = array();
        foreach($currentEndpoints as $p){
            if($p->arrived !== NULL){
                $arrivedEndpoints[] = $p->location_id;
            }else{
                $unarrivedEndpoints[] = $p;
            }
        }
        return array($arrivedEndpoints,$unarrivedEndpoints);
    }
    
    public function setEndpoints(array $input) {
        if (array_key_exists('endpoints', $input)) {
            $currentEndpoints = $this->separateEndpoints();
//            $arrivedEndpoints = $currentEndpoints[0];
            $unarrivedEndpoints = $currentEndpoints[1];
            
            $this->detachEndpoints($unarrivedEndpoints);
            $this->attachEndpoints($input['endpoints']);
        }
    }
    private function detachEndpoints(array $endpoints){
        foreach($endpoints as $e) {
            $e->delete();
        }
    }
    
    private function attachEndpoints(array $endpoints){
        foreach($endpoints as $e) {
            $ep = new JourneyEndpoint();
            $ep->journey_id = $this->journey_id;
            $ep->location_id = $e;
            $this->endpoints()->save($ep);
        }
    }
    
    
    public static function uncompletedForWorker(Worker $worker) {
        $res = self::where('worker_id', '=', $worker->worker_id)
                        ->where('ts_finished', '=', 0)->first();
        if ($res) {
            return $res;
        }
        return false;
    }
    
    public function lastCheckin(){
        return $this->checkins()->orderBy('ts','desc')->limit(1)->get()->first();
    }
    public function requiresHalfTimeCheckin() {
        $duration = $this->time_estimate / 2;
        $expectedHalfTime = strtotime($this->started_at) + $duration * 3600;
        if ($expectedHalfTime <= time()) {
            return true;
        }
        return false;
    }

    public function requiresFulltimeCheckin() {
        $duration = $this->time_estimate;
        $expectedHalfTime = strtotime($this->started_at) + $duration * 3600;
        if ($expectedHalfTime <= time()) {
            return true;
        }
        return false;
    }

    public function hasHalftimeCheckin() {
        $duration = $this->time_estimate * 30;
        return $this->hasCheckingAfter($duration);
    }

    public function hasFulltimeCheckin() {
        $duration = $this->time_estimate * 60;
        return $this->hasCheckingAfter($duration);
    }

    private function hasCheckingAfter($time) {
//            dd($this->checkins()->where('is_active','=','1')->whereRaw("created_at >= DATE_ADD('{$this->started_at}', INTERVAL {$time} MINUTE)")->get());
        return $this->checkins()->where('is_active', '=', '1')->whereRaw("created_at >= DATE_ADD('{$this->started_at}', INTERVAL {$time} MINUTE)")->get()->count();
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return self::with('addedBy', 'journeyFrom', 'endpoints.location', 'checkins')->orderBy('started_at', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('addedBy', 'journeyFrom', 'endpoints.location', 'checkins')
                    ->where('deleted_at', '=', NULL)
                    ->orderBy('started_at', 'desc')
                    ->whereIn('worker_id', $companyWorkers)
                    ->get()->all();
        }
    }

    public static function getRecentJourneysForCompany(Admin $admin, $amount) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        //with('addedBy', 'journeyFrom', 'endpoints.location', 'checkins') // Don't need this for now
        return self::where('deleted_at', '=', NULL)
                ->orderBy('ts_created', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->take(5)
                ->get();
    }

    // $month parameter format is YYYY-MM
    public static function getForCompanyByMonth($admin, $month) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        //with('addedBy', 'journeyFrom', 'endpoints.location', 'checkins') // Don't need this for now
        return self::where('deleted_at', '=', NULL)
                ->where('created_at', '>=', $month . '-01 00:00:00')
                ->where('created_at', '<=', $month . '-31 23:59:59')
                ->orderBy('ts_created', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->get()
                ->all();
    }

    public static function checkinsMissedForCompany(Admin $admin) {
        $companyGroups = Group::getAllForCompany($admin->company_id);
        if (empty($companyGroups)) {
            return array();
        }
        $journeys = self::with('addedBy', 'journeyFrom', 'journeyTo')
                        ->where('finished_at', '=', NULL)
                        ->select('journeys.*')
                        ->join('workers', 'workers.worker_id', '=', 'journeys.worker_id')
                        ->whereIn('workers.group_id', $companyGroups)->get()->all();

        $res = array();
        foreach ($journeys as $journey) {
            if (($journey->requiresHalftimeCheckin() && !$journey->hasHalftimeCheckin()) OR ( $journey->requiresFulltimeCheckin() && !$journey->hasFulltimeCheckin())) {
                $res[] = $journey;
            }
        }
        return $res;
    }

    public function getDates() {
        return array();
    }

}

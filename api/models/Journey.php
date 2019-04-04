<?php

class Journey extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journeys';
    protected $primaryKey = 'journey_id';
    public $timestamps = false;
    private static $properties = array('time_estimate', 'distance_estimate', 'started_at',
        'created_at', 'location_from', 'location_to');

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function journeyFrom() {
        return $this->belongsTo('JourneyLocation', 'location_from');
    }

    public function journeyTo() {
        return $this->belongsTo('JourneyLocation', 'location_to');
    }

    public function checkins() {
        return $this->hasMany('JourneyCheckin', 'journey_id');
    }

    public function expectedHalftimeCheckinTime() {
        $duration = $this->time_estimate / 2;
        return date('Y-m-d H:i:s', strtotime($this->started_at) + $duration * 3600);
    }

    public function expectedFulltimeCheckinTime() {
        $duration = $this->time_estimate;
        return date('Y-m-d H:i:s', strtotime($this->started_at) + $duration * 3600);
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

    public static function uncompletedForWorker(Worker $worker) {
        $res = self::where('worker_id', '=', $worker->worker_id)
                        ->where('ts_finished', '=', 0)->first();
        if ($res) {
            return $res;
        }
        return false;
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
            return self::with('addedBy', 'journeyFrom', 'journeyTo', 'checkins')->orderBy('started_at', 'desc')->get()->all();
        } else {
            $companyWorkers = Worker::getUserIdsForCompany($admin);
            if (empty($companyWorkers)) {
                return array();
            }
            return self::with('addedBy', 'journeyFrom', 'journeyTo', 'checkins')->orderBy('started_at', 'desc')->whereIn('worker_id', $companyWorkers)->get()->all();
        }
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

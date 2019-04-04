<?php

class JourneyLocation extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journey_locations';
    protected $primaryKey = 'location_id';
    public $timestamps = false;
    private static $properties = array('title', 'specific_location', 'lsd', 'wellpad',
        'road', 'description', 'company', 'street', 'suite', 'city', 'zip', 'state', 'type');

    CONST HOME_TYPE = 0;
    CONST WORK_TYPE = 1;
    CONST SITE_TYPE = 2;

    private static $typeListKeys = array(
        self::HOME_TYPE => 'homeAddressList',
        self::WORK_TYPE => 'workAddressList',
        self::SITE_TYPE => 'siteAddressList'
    );

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
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
    
    public function journey() {
        return $this->belongsToMany('JourneyLocation', 'journeys_v2_has_endpoints', 'journey_id', 'location_id');
    }

    public static function getAllForWorker(Worker $worker) {
        $locations = self::where('worker_id', '=', $worker->worker_id)->get()->all();
        $result = array();

        foreach ($locations as $l) {
            $result[self::$typeListKeys[$l->type]][] = $l->toArray();
        }
        if ($result) {
            return $result;
        }
        return new stdClass();
    }

}

<?php

class JourneyCheckin extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journey_checkins';
    protected $primaryKey = 'checkin_id';
    public $timestamps = false;
    private static $properties = array('latitude', 'longitude', 'journey_finished',
        'created_at', 'is_active', 'is_worker_added', 'ts');

    public function journey() {
        return $this->belongsTo('Journey', 'journey_id');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function getDates() {
        return array();
    }

}

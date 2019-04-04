<?php

class TailgateSignoffWorker extends Eloquent {

    protected $table = 'tailgate_signoff_workers';
    protected $primaryKey = 'signoff_worker_id';
    public $timestamps = false;
    public $fillable = array('first_name', 'last_name', 'tailgate_id');
    private static $properties = array('first_name', 'last_name');

    public function getProperties() {
        return self::$properties;
    }

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }
    
    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

}

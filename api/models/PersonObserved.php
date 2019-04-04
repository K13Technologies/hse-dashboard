<?php

class PersonObserved extends Eloquent {

    protected $table = 'positive_observation_persons';
    protected $primaryKey = 'person_id';
    public $timestamps = false;
    private static $properties = array('name', 'company');

    public function positiveObservation() {
        return $this->belongsTo('PositiveObservation', 'positive_observation_id');
    }

    public function setFields(array $input) {

        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            } else {
                return false;
            }
        }
        return $this;
    }

}

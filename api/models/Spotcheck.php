<?php

class Spotcheck extends Eloquent {

    protected $table = 'flha_spotchecks';
    protected $primaryKey = 'spotcheck_id';
    public $timestamps = false;
    protected $softDelete = true;
    public $fillable = array('ts', 'created_at', 'flha_id', 'first_name', 'last_name', 'position', 'company', 'flha_validity',
        'flha_validity_description', 'critical_hazard', 'critical_hazard_description',
        'crew_list_complete', 'crew_description');
    private static $properties = array('first_name', 'last_name', 'position', 'company', 'flha_validity',
        'flha_validity_description', 'critical_hazard', 'critical_hazard_description',
        'crew_list_complete', 'crew_description'
    );

    public function getProperties() {
        return self::$properties;
    }

    public function flha() {
        return $this->belongsTo('Flha', 'flha_id');
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

<?php

class FlhaTaskHazard extends Eloquent {

    protected $table = 'flha_task_hazards';
    protected $primaryKey = 'flha_task_hazard_id';
    public $timestamps = true;
    protected $softDelete = true;
    public $fillable = array('description', 'risk_level', 'eliminate_hazard', 'risk_assessment', 'flha_task_id');
    // Be careful when changing these values -- anywhere setFields is used, it could break certain functionalities
    private static $properties = array('description', 'risk_level', 'eliminate_hazard', 'risk_assessment'); 

    public function getProperties() {
        return self::$properties;
    }

    public function flhaTask() {
        return $this->belongsTo('FlhaTask', 'flha_task_id');
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

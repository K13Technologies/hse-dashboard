<?php

class TailgateTaskHazard extends Eloquent {

    protected $table = 'tailgate_task_hazards';
    protected $primaryKey = 'tailgate_task_hazard_id';
    public $timestamps = false;
    public $fillable = array('description', 'tailgate_task_id', 'risk_level', 'eliminate_hazard','risk_assessment');
    private static $properties = array('description', 'risk_level', 'eliminate_hazard', 'risk_assessment', 'tailgate_task_id');

    public function getProperties() {
        return self::$properties;
    }

    public function tailgateTask() {
        return $this->belongsTo('TailgateTask', 'tailate_task_id');
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

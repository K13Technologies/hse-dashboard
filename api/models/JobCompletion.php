<?php

class JobCompletion extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'job_completions';
    protected $primaryKey = 'job_completion_id';
    public $timestamps = false;
    private static $properties = array('permit_closed', 'permit_closed_description', 'hazard_remaining',
        'hazard_remaining_description', 'flagging_removed', 'flagging_removed_description',
        'incidents_reported', 'incidents_reported_description', 'concerns', 'concerns_description', 'equipment_removed', 'equipment_removed_description',
        'created_at');

    public function completioner() {
        return $this->morphTo();
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

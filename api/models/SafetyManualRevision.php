<?php

class SafetyManualRevision extends Eloquent {

    protected $table = 'safety_manual_revisions';
    protected $primaryKey = 'revision_id';

    private static $properties = array('safety_manual_id','admin_id', 'ip_address');
    protected $fillable = array('safety_manual_id', 'admin_id', 'ip_address');

    public function safetyManual() {
        return $this->belongsTo('SafetyManual', 'safety_manual_id');
    }

    public function admin() {
        return $this->belongsTo('Admin', 'admin_id');
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
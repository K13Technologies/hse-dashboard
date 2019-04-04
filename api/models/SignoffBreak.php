<?php

class SignoffBreak extends Eloquent {

    protected $table = 'flha_signoff_breaks';
    protected $primaryKey = 'signoff_break_id';
    public $timestamps = false;
    protected $softDelete = false;

    const MORNING_BREAK = 0;
    const LUNCH_BREAK = 1;
    const AFTERNOON_BREAK = 2;

    public function getType() {
        switch ($this->type) {
            case self::MORNING_BREAK:
                return "Morning";
            case self::LUNCH_BREAK:
                return "Lunch";
            case self::AFTERNOON_BREAK:
                return "Evening";
            default:
                return "";
        }
    }

    public $fillable = array('ts', 'created_at', 'signoff_worker_id');
    private static $properties = array('type');

    public function getProperties() {
        return self::$properties;
    }

    public function signoffWorker() {
        return $this->belongsTo('SignoffWorker', 'signoff_worker_id');
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

<?php

class SafetyManualSubsection extends Eloquent {

    protected $table = 'safety_manual_subsections';
    protected $primaryKey = 'subsection_id';
    private static $properties = array('subsection_title','subsection_content');
    protected $fillable = array('safety_manual_id', 'section_id', 'subsection_title', 'subsection_content');

    public function safetyManualSection() {
        return $this->belongsTo('SafetyManualSection', 'section_id');
    }

    public function safetyManual() {
        return $this->belongsTo('SafetyManual', 'safety_manual_id');
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
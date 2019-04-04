<?php

class SafetyManualSection extends Eloquent {

    protected $table = 'safety_manual_sections';
    protected $primaryKey = 'section_id';
    private static $properties = array('section_title');
    protected $fillable = array('section_title', 'safety_manual_id', 'section_description');

    public function setFields(array $input) {
        foreach (Self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function subsections() {
        return $this->hasMany('SafetyManualSubsection', 'section_id');
    }

    public function safetyManual(){
        return $this->belongsTo('SafetyManual', 'safety_manual_id');
    }

    public function companyId() {
        return $this->safetyManual()->company_id;
    }
}
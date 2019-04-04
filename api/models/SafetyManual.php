<?php

class SafetyManual extends Eloquent {

    protected $table = 'safety_manuals';
    protected $primaryKey = 'safety_manual_id';
    private static $properties = array('manual_content');
    protected $fillable = array('manual_content');

    public static function getFullManualForCompany($companyId){
    	$safetyManual = Self::with('sections.subsections')
    						->where('company_id', '=', $companyId)
    						->get()
    						->first();

        return $safetyManual;
    }

    public function sections() {
        return $this->hasMany('SafetyManualSection', 'safety_manual_id');
    }

    public function subsections() {
        return $this->hasMany('SafetyManualSubsection', 'safety_manual_id');
    }

    public function company() {
        return $this->belongsTo('Company', 'company_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }
}
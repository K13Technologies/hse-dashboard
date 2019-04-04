<?php

class Division extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'divisions';
    protected $primaryKey = 'division_id';
    public $timestamps = false;

//           Company,    Division,   Business unit,    Group + project

    public function company() {
        return $this->belongsTo('Company', 'company_id');
    }

    public function businessUnits() {
        return $this->hasMany('BusinessUnit', 'division_id');
    }

    public function isUniqueForCompany() {
        $company = Company::find($this->company_id);
        $existing = $company->divisions()->where('division_name', '=', $this->division_name)->get()->first();
        if (!$existing instanceOf self || $existing->division_id == $this->division_id) {
            return true;
        }
        return false;
    }

}

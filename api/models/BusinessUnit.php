<?php

class BusinessUnit extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'business_units';
    protected $primaryKey = 'business_unit_id';
    public $timestamps = false;

//           Company,    Division,   Business unit,    Group

    public function division() {
        return $this->belongsTo('Division', 'division_id');
    }

    public function groups() {
        return $this->hasMany('Group', 'business_unit_id');
    }

    public function isUniqueForDivision() {
        $division = Division::find($this->division_id);
        $existing = $division->businessUnits()->where('business_unit_name', '=', $this->business_unit_name)->get()->first();
        if (!$existing instanceOf self || $existing->business_unit_id == $this->business_unit_id) {
            return true;
        }
        return false;
    }

}

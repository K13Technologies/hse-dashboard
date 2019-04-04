<?php

class Client extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clients';
    protected $primaryKey = 'client_id';
    public $timestamps = false;

    public function project() {
        return $this->belongsTo('Project', 'project_id');
    }

    public function group() {
        return $this->belongsTo('Group', 'group_id');
    }

    public function businessUnit() {
        return $this->belongsTo('BusinessUnit', 'business_unit_id');
    }

    public function division() {
        return $this->belongsTo('Division', 'division_id');
    }

    public function company() {
        return $this->belongsTo('Company', 'company_id');
    }

}

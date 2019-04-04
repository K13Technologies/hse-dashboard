<?php

class InspectionAction extends Eloquent {

    protected $table = 'inspection_actions';
    protected $primaryKey = 'inspection_action_id';
    public $timestamps = true;

    public function inspection() {
        return $this->belongsTo('Inspection', 'inspection_id');
    }

}

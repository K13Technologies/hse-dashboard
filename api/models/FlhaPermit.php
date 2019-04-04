<?php

class FlhaPermit extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flha_permits';
    protected $primaryKey = 'flha_permit_id';
    public $timestamps = false;

    public function flha() {
        return $this->belongsTo('Flha', 'tailgate_id');
    }

}

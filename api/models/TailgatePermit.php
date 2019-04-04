<?php

class TailgatePermit extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tailgate_permits';
    protected $primaryKey = 'tailgate_permit_id';
    public $timestamps = false;

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }

}

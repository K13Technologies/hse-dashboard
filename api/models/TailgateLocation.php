<?php

class TailgateLocation extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tailgate_locations';
    protected $primaryKey = 'tailgate_location_id';
    public $timestamps = false;

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }

}

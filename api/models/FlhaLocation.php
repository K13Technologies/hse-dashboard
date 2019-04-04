<?php

class FlhaLocation extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flha_locations';
    protected $primaryKey = 'flha_location_id';
    public $timestamps = false;

    public function flha() {
        return $this->belongsTo('Flha', 'tailgate_id');
    }

}

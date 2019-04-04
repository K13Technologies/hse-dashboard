<?php

class TailgateLSD extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tailgate_lsds';
    protected $primaryKey = 'tailgate_lsd_id';
    public $timestamps = false;

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }

}

<?php

class TailgateSupervisor extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tailgate_supervisors';
    protected $primaryKey = 'tailgate_supervisor_id';
    public $timestamps = false;

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }

}

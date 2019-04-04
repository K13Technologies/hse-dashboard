<?php

class FlhaLSD extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flha_lsds';
    protected $primaryKey = 'flha_lsd_id';
    public $timestamps = false;

    public function flha() {
        return $this->belongsTo('Flha', 'tailgate_id');
    }

}

<?php

class FlhaSite extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'flha_sites';
    protected $primaryKey = 'flha_site_id';
    public $timestamps = false;

    public function flha() {
        return $this->belongsTo('Flha', 'tailgate_id');
    }

}

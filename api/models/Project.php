<?php

class Project extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'projects';
    protected $primaryKey = 'project_id';
    public $timestamps = false;

    public function group() {
        return $this->belongsTo('Group', 'group_id');
    }

}

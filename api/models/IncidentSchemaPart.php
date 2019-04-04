<?php

class IncidentSchemaPart extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_schema_parts';
    protected $primaryKey = 'incident_schema_part_id';
    public $timestamps = false;

    public function schema() {
        return $this->belongsTo('IncidentSchema', 'incident_schema_id');
    }

}

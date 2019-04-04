<?php

class IncidentPartStatement extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_part_statements';
    protected $primaryKey = 'part_statement_id';
    public $timestamps = false;
    public $fillable = array('incident_id', 'incident_schema_part_id', 'statementable_id', 'statementable_type');

    public function incident() {
        return $this->belongsTo('Incident', 'incident_id');
    }

    public function schemaPart() {
        return $this->belongsTo('IncidentSchemaPart', 'incident_schema_part_id');
    }

    public function getType() {
        return $this->schemaPart->schema->type;
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function statementable() {
        return $this->morphTo();
    }

    public function extractPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        unset($this->photos);
        return $ids;
    }

}

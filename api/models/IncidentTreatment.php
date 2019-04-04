<?php

class IncidentTreatment extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_treatments';
    protected $primaryKey = 'incident_treatment_id';
    public $timestamps = false;
    public $fillable = array('incident_id', 'incident_type_id');
    private static $properties = array("first_aid", "medical_aid", "responder_name",
                 "responder_company", "responder_phone_number", "comment", "injuries");

    public function incident() {
        return $this->belongsTo('Incident', 'incident_id');
    }

    public function type() {
        return $this->belongsTo('IncidentType', 'incident_type_id');
    }
    
    public function setFields(array $input) {
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)){
                $this->$key = trim($val);
            }
        }
        return $this;
    }
    
    public function statements() {
        return $this->morphMany('IncidentPartStatement', 'statementable');
    }

    public function getWithDetails() {
        $treatmentStatements = array();

        foreach ($this->statements as $s) {
            $s->photoIds = $s->extractPhotoIds();
            $treatmentStatements[] = $s;
        }
        unset($this->statements);
        $this->parts = \Illuminate\Database\Eloquent\Collection::make($treatmentStatements)->toArray();
        return $this;
    }

    public static function saveTreatment($incident, $input) {
        $object = self::firstOrNew(array('incident_id' => $incident->incident_id,
                    'incident_type_id' => $input['incident_type_id']));
        if ($object->incident_treatment_id) {
            return $object->incident_treatment_id;
        } else {
            $object->setFields($input);
            if ($object->save()) {
                return $object->incident_treatment_id;
            }
            return false;
        }
    }

}

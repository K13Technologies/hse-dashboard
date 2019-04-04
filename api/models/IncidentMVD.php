<?php

class IncidentMVD extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_mvds';
    protected $primaryKey = 'incident_mvd_id';
    public $timestamps = false;

    const TYPE_TRUCK = 0;
    const TYPE_TRAILER = 1;

    public static $vehicle_types = array(self::TYPE_TRUCK => " Pickup Truck", self::TYPE_TRAILER => " Tractor Trailer");

    public $fillable = array('incident_id', 'incident_type_id');
    private static $properties = array("driver_license_number", "insurance_company", "insurance_policy_number", "policy_expiry_date",
        "vehicle_year", "make", "model", "vin", "color", "license_plate", "time_of_incident", "tdg",
        "tdg_material", "wearing_seatbelts", "wearing_seatbelts_description", "airbags_deployed",
        "damage_exceeds_amount", "police_file_number", "attending_police_officer", "police_service",
        "police_name", "police_badge_number", "police_business_phone_number", "vehicle_towed",
        "tow_company", "tow_driver_name", "tow_business_phone_number", "tow_address",'my_vehicle_damaged',
        "other_passengers", "other_passengers_details", "vehicles_involved", "comment", "vehicleType");

    public function type() {
        return $this->belongsTo('IncidentType', 'incident_type_id');
    }

    public function incident() {
        return $this->belongsTo('Incident', 'incident_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function statements() {
        return $this->morphMany('IncidentPartStatement', 'statementable');
    }

    public function setFields(array $input) {
        if (array_key_exists('time_of_incident', $input)) {
            $input['ts_of_incident'] = WKSSDate::getTsFromDateWithTz($input['time_of_incident']);
        }
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)){
                $this->$key = trim($val);
            }
        }
        return $this;
    }

    public function getWithDetails() {
        $mvdStatements = array();

        foreach ($this->statements as $s) {
            $s->photoIds = $s->extractPhotoIds();
            $mvdStatements[] = $s;
        }
        unset($this->statements);
        $this->parts = \Illuminate\Database\Eloquent\Collection::make($mvdStatements)->toArray();
        $this->photoIds = $this->extractPhotoIds();

        return $this;
    }

    public static function saveMVD($incident, $input) {

        $object = self::firstOrNew(array('incident_id' => $incident->incident_id,
                    'incident_type_id' => $input['incident_type_id']));
        if ($object->incident_mvd_id) {
            return $object->incident_mvd_id;
        } else {
            $object->setFields($input);
            if ($object->save()) {
                return $object->incident_mvd_id;
            }
            return false;
        }
    }

    public function extractPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        unset($this->photos);
        return $ids;
    }

    public static function getVehicleTypes() {
        return self::$vehicle_types;
    }

}

<?php

class IncidentReleaseSpill extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_release_spills';
    protected $primaryKey = 'incident_release_spill_id';
    public $timestamps = false;
    public $fillable = array('incident_id', 'incident_type_id',  "commodity", "potential_exposure", "release_source",
        "release_to", "quantity_released", "quantity_released_unit", "quantity_recovered",
        "quantity_recovered_unit", "comment");
    private static $properties = array("commodity", "potential_exposure", "release_source",
        "release_to", "quantity_released", "quantity_released_unit", "quantity_recovered",
        "quantity_recovered_unit", "comment");

    // Constants which define the different types of measurements. These can be added to a database table later on.
    const UOM_ML = 0; // Milliliters
    const UOM_L = 1;  // Liters
    const UOM_M3 = 2; // Cubic Meters
    const UOM_GA = 3; // Gallons
    const UOM_BOE = 4;// Barrels (barrel of oil equivalent)

    private static $uomTypeList = array(self::UOM_ML => 'Milliliters', self::UOM_L => 'Liters', self::UOM_M3 => 'Cubic Meters', self::UOM_GA => 'Gallons', self::UOM_BOE => 'Barrels');

    public function incident() {
        return $this->belongsTo('Incident', 'incident_id');
    }

    public function setFields(array $input) {
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)){
                $this->$key = trim($val);
            }
        }
        return $this;
    }

    public static function saveReleaseSpill($incident, $input) {
        $object = self::firstOrNew(array('incident_id' => $incident->incident_id));
        if ($object->incident_release_spill_id) {
            return $object->incident_release_spill_id;
        } else {
            $object->setFields($input);
            if ($object->save()) {
                return $object->incident_release_spill_id;
            }
            return false;
        }
    }

    public static function getUOMTypeList() {
        return self::$uomTypeList;
    }

}

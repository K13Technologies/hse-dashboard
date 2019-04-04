<?php

class IncidentType extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_types';
    protected $primaryKey = 'incident_type_id';
    public $timestamps = false;
    public static $medical = array(1, 2, 3, 4, 5);
    public static $mvd = array(8, 9, 10);
    public static $release_and_spill = array(6);

    public static function rename($from, $to) {
        $a = self::where('type_name', '=', $from)->first();
        if ($a) {
            $a->type_name = $to;
            $a->save();
        }
    }

    public static function add($typeName) {
        $c = new self();
        $c->type_name = $typeName;
        $c->save();
    }

    public static function remove($typeName) {
        self::where('type_name', '=', $typeName)->first()->delete();
    }
    
    public function isMedical(){
        return in_array($this->incident_type_id, self::$medical);
    }
    public function isMVD(){
        return in_array($this->incident_type_id, self::$mvd);
    }
    public function isReleaseAndSpill(){
        return in_array($this->incident_type_id, self::$release_and_spill);
    }
}

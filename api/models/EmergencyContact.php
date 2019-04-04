<?php

class EmergencyContact extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'emergency_contacts';
    protected $primaryKey = 'emergency_contact_id';
    public $timestamps = false;
    public $fillable = array('name', 'contact', 'relationship');
    private static $properties = array('name', 'contact', 'relationship');

    public function worker() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

}

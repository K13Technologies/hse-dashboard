<?php

class TailgateSignoffVisitor extends Eloquent {

    protected $table = 'tailgate_signoff_visitors';
    protected $primaryKey = 'signoff_visitor_id';
    public $timestamps = false;
    public $fillable = array('first_name', 'last_name', 'flha_id');
    private static $properties = array('first_name', 'last_name');

    public function getProperties() {
        return self::$properties;
    }

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
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

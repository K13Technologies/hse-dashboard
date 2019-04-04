<?php

class SignoffVisitor extends Eloquent {

    protected $table = 'flha_signoff_visitors';
    protected $primaryKey = 'signoff_visitor_id';
    public $timestamps = false;
    protected $softDelete = false;
    public $fillable = array('first_name', 'last_name', 'flha_id');
    private static $properties = array('first_name', 'last_name', 'created_at');

    public function getProperties() {
        return self::$properties;
    }

    public function flha() {
        return $this->belongsTo('Flha', 'flha_id');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function getDates() {
        return array();
    }

}

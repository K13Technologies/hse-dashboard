<?php

class HazardChecklistCategory extends Eloquent {

    protected $table = 'flha_hazard_categories';
    protected $primaryKey = 'flha_hazard_category_id';
    public $timestamps = false;

    public function hazardChecklistItems() {
        return $this->hasMany('HazardChecklistItem', 'flha_hazard_category_id');
    }

    public static function rename($from, $to) {
        $c = self::where('category_name', '=', $from)->first();
        if ($c) {
            $c->category_name = $to;
            $c->save();
        }
    }

    public function formatString() {
        $part = strtolower($this->category_name);
        $part = str_replace(' and ', ' & ', $part);
        $result = array();
        foreach (explode('/', $part) as $s) {
            $result[] = ucwords($s);
        }
        $result = implode('/', $result);
        $result = str_replace('Ppe', 'PPE', $result);
        $this->category_name = $result;
        return $this;
    }

}

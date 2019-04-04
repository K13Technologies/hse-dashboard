<?php

class HazardChecklistItem extends Eloquent {

    protected $table = 'flha_hazard_items';
    protected $primaryKey = 'flha_hazard_item_id';
    public $timestamps = false;

    public function hazardChecklistCategory() {
        return $this->belongsTo('HazardChecklistCategory', 'flha_hazard_category_id');
    }

    public static function rename($from, $to) {
        $c = self::where('item_name', '=', $from)->first();
        if ($c) {
            $c->item_name = $to;
            $c->save();
        }
    }

    public static function add($itemName, $categoryId) {
        $c = new self();
        $c->item_name = $itemName;
        $c->flha_hazard_category_id = $categoryId;
        $c->save();
    }

    public static function remove($itemName) {
        self::where('item_name', '=', $itemName)->get()->each(function($e) {
            $e->delete();
        });
    }

    public function formatString() {
        $search = array('( ', ' )');
        $replace = array('(', ')');
        $part = strtolower(trim($this->item_name));
        $part = str_replace($search, $replace, $part);
        $part = ucfirst(strtolower($part));
        $result = array();
        foreach (explode('/', $part) as $s) {
            $result[] = ucfirst($s);
        }
        $result = implode('/', $result);
        $this->item_name = $result;
        return $this;
    }

}

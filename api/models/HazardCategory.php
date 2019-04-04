<?php

class HazardCategory extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hazard_categories';
    protected $primaryKey = 'hazard_category_id';
    public $timestamps = false;

    public static function rename($from, $to) {
        $c = self::where('category_name', '=', $from)->first();
        if ($c) {
            $c->category_name = $to;
            $c->save();
        }
    }

    public static function add($categoryName) {
        $c = new self();
        $c->category_name = $categoryName;
        $c->save();
    }

    public static function remove($categoryName) {
        self::where('category_name', '=', $categoryName)->first()->delete();
    }

    public function formatString() {
        $search = array('( ', ' )');
        $replace = array('(', ')');
        $part = strtolower($this->category_name);
        $part = str_replace(' and ', ' & ', $part);
        $part = ucwords(strtolower($part));
        $this->category_name = $part;
        return $this;
    }

}

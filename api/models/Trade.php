<?php

class Trade extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'trades';
    protected $primaryKey = 'trade_id';
    public $timestamps = false;

    public static function getIdByTradeName($tradeName) {
        $trade = self::where('trade_name', 'like', $tradeName)->get();
        if ($trade->count()) {
            return $trade->first()->trade_id;
        }
        return null;
    }

    public static function rename($from, $to) {
        $a = self::where('trades', '=', $from)->first();
        if ($a) {
            $a->trade_name = $to;
            $a->save();
        }
    }

    public function formatString() {
        $search = array('( ', ' )');
        $replace = array('(', ')');
        $part = strtolower($this->trade_name);
        $part = str_replace($search, $replace, $part);
        $part = ucfirst(strtolower($part));
        $result = array();
        foreach (explode('/', $part) as $s) {
            $result[] = ucfirst($s);
        }
        $result = implode('/', $result);
        $result = str_replace('Mwd', 'MWD', $result);
        $this->trade_name = $result;
        return $this;
    }

}

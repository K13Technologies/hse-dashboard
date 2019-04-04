<?php

class HazardActivity extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hazard_activities';
    protected $primaryKey = 'hazard_activity_id';
    public $timestamps = false;

    public static function rename($from, $to) {
        $a = self::where('activity_name', '=', $from)->first();
        if ($a) {
            $a->activity_name = $to;
            $a->save();
        }
    }

    public static function add($activityName) {
        $c = new self();
        $c->activity_name = $activityName;
        $c->save();
    }

    public static function remove($activityName) {
        self::where('activity_name', '=', $activityName)->first()->delete();
    }

}

<?php

class Activity extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'activities';
    protected $primaryKey = 'activity_id';
    public $timestamps = false;
    protected $hidden = array('hazard', 'po', 'near_miss', 'incident');

    public static function rename($from, $to) {
        $a = self::where('activity_name', '=', $from)->first();
        if ($a) {
            $a->activity_name = $to;
            $a->save();
        }
    }

    public static function add($activityName, $hazard = true, $po = true, $nm = true, $incident = true) {
        $c = new self();
        $c->activity_name = $activityName;
        $c->hazard = $hazard;
        $c->po = $po;
        $c->near_miss = $nm;
        $c->incident = $incident;
        $c->save();
    }

    public static function remove($activityName) {
        self::where('activity_name', '=', $activityName)->first()->delete();
    }

}

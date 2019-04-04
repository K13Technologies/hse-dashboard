<?php

class PositiveObservationActivity extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'positive_observation_activities';
    protected $primaryKey = 'positive_observation_activity_id';
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

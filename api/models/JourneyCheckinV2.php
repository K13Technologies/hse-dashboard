<?php

class JourneyCheckinV2 extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journey_checkins_v2';
    protected $primaryKey = 'checkin_id';
    public $timestamps = false;
    private static $properties = array('latitude', 'longitude',
        'created_at', 'is_active', 'is_worker_added', 'ts');

    public function journey() {
        return $this->belongsTo('Journey', 'journey_id');
    }
    
    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }
    
    public static function lastCheckinForEndpoint(JourneyEndpoint $endp){
        return self::where('journey_id','=',$endp->journey_id)
                     ->where('ts','>=',$endp->ts_arrived)
                    ->orderBy('ts','desc')
                    ->limit(1)
                    ->where('is_active','=',1)
                    ->get()->first();
    }
    
    public function getDates() {
        return array();
    }

}

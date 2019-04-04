<?php

class JourneyEndpoint extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'journeys_v2_has_endpoints';
    protected $primaryKey = 'journey_endpoint_id';
    public $timestamps = false;

    public function journey() {
        return $this->belongsTo('JourneyV2', 'journey_id');
    }
    
    public function location() {
        return $this->belongsTo('JourneyLocation', 'location_id');
    }

    public static function getAllForJourney(Journey $journey) {
        $endpoints = self::where('journey_id', '=', $journey->journey_id)->get()->all();
        return $endpoints;
    }

}

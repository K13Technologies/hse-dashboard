<?php

class Inspection extends Eloquent {

    protected $table = 'inspections';
    protected $primaryKey = 'inspection_id';
    public $timestamps = false;
    public $hidden = array('photos');
    public $fillable = array('ts', 'created_at', 'vehicle_id', 'worker_id');
    private static $properties = array('location', 'mileage', 'comment',
        'visual_dents', 'visual_tires', 'visual_rims', 'visual_mud_flaps', 'visual_lights_and_reflections',
        'visual_broken_headlights', 'visual_broken_taillights', 'visual_license_plate',
        'visual_mirrors', 'visual_spare_tire', 'visual_windshield',
        'engine_coolant', 'engine_battery', 'engine_brakes', 'engine_heater', 'engine_defroster',
        'engine_horn', 'engine_radiator', 'engine_engine', 'engine_muffler', 'visual_wipers', 'interior_booster_cables',
        'interior_first_aid', 'interior_fire_extinguisher', 'interior_flare_kit', 'interior_warning_triangles',
        'interior_tow_rope', 'interior_whip_flag', 'interior_radio', 'interior_insurance', 'interior_registration',
        'interior_vehicle_jack', 'interior_tire_wrench', 'interior_seatbelts');
    public static $formTypeName = 'Inspection';

    public static function formTypeName () {
        return self::$formTypeName;
    }

    public static function getProperties() {
        return self::$properties;
    }

    public function getEngineProperties() {
        $f = array_filter(self::$properties, function ($k) {
            return preg_match('/^engine./', $k);
        });
        $b = array_intersect_key(self::$properties, $f);
        return $b;
    }

    public function getInteriorProperties() {
        $f = array_filter(self::$properties, function ($k) {
            return preg_match('/^interior./', $k);
        });
        $b = array_intersect_key(self::$properties, $f);
        return $b;
    }

    public function getVisualProperties() {
        $f = array_filter(self::$properties, function ($k) {
            return preg_match('/^visual./', $k);
        });
        $b = array_intersect_key(self::$properties, $f);
        return $b;
    }

    public function vehicle() {
        return $this->belongsTo('Vehicle', 'vehicle_id');
    }

    public function addedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function actions() {
        return $this->hasMany('InspectionAction', 'inspection_id');
    }

    public function actionsCompleted() {
        return $this->hasMany('InspectionAction', 'inspection_id')
                        ->orderBy('completed_on', 'desc');
    }

    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            $this->has_nulls = false;
            if (array_key_exists($key, $input)) {
                $this->$key = trim($input[$key]);
                if ($key === 'mileage') {
                    $this->vehicle->mileage = $input[$key];
                }
            }else{
                $this->$key = NULL;
                $this->has_nulls = !$this->has_nulls?TRUE:FALSE;
            }
        }
        $this->vehicle->updated_at = date('Y-m-d H:i:s');

        return $this;
    }

    public static function getWithDetails($inspectionId) {
        $inspection = self::find($inspectionId);
        foreach (self::$properties as $p) {

            if ($inspection->$p === NULL) {
                unset($inspection->$p);
            } else {
                foreach ($inspection->actions as $a) {
                    if ($a->is_worker_added && $a->action_type == $p) {
                        $cAKey = $p . '_ca';
                        $cAAKey = $p . '_ca_a';
                        $inspection->$cAKey = $a->action;
                        $inspection->$cAAKey = is_null($a->completed_on) ? "0" : "1";
                    }
                }
            }
        }
        unset($inspection->actions);
        $inspection->photoList = $inspection->extractPhotoIds();
        return $inspection;
    }

    public static function getAllByVehicleId($vehicleId) {
        $result = self::where('vehicle_id', '=', $vehicleId)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;
    }

    public static function getRecentInspectionsForCompany(Admin $admin, $amount) {
        /*$result = self::where('vehicle_id', '=', $vehicleId)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result;*/

        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::orderBy('ts', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->take($amount)
                ->get();
    }

    // $month parameter format is YYYY-MM
    public static function getForCompanyByMonth($admin, $month) {
        $companyWorkers = Worker::getUserIdsForCompany($admin);
        if (empty($companyWorkers)) {
            return array();
        }
        return self::orderBy('ts', 'desc')
                ->whereIn('worker_id', $companyWorkers)
                ->where('created_at', '>=', $month . '-01 00:00:00')
                ->where('created_at', '<=', $month . '-31 23:59:59')
                ->get()
                ->all();
    }

    public function extractPhotoIds() {
        $ids = array();
        $photoArray = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
            if (!empty($p->used_for)) {
                $key = $p->used_for . "_photoList";
                $photoArray[$key][] = $p->name;
            }
        }
        foreach ($photoArray as $key => $val){
            $this->$key = $val;
        }
        return $ids;
    }

    public function buildActions($input = NULL) {
        foreach (self::$properties as $key) {
            $val = $this->$key;
            if ((preg_match("/^interior.*|engine.*|visual.*/", $key) && !empty($val))) {
                $action = new InspectionAction();
                $cAkey = $key . '_ca';
                if (is_array($input) && array_key_exists($cAkey, $input)) {
                    $correctiveAction = $input[$cAkey];
                    $action->action = $correctiveAction;
                    $action->is_worker_added = true;
                }
                $cAAkey = $key . '_ca_a';
                if (is_array($input) && array_key_exists($cAAkey, $input) && (int) $input[$cAAkey] == 1) {
                    $action->completed_on = date('Y-m-d');
                }

                $action->action_type = $key;
                $this->actions()->save($action);

                $this->action_required = true;
            }
        }
        $this->save();
    }

    public function unresolvedActionsCount() {
        return $this->actions()->where('completed_on', '=', null)->count();
    }

    public function getDates() {
        return array();
    }
    
    public function toArrayNoNulls(){
        foreach (self::$properties as $p) {
            if ($this->$p === NULL) {
                unset($this->$p);
            } 
        }
        return $this->toArray();
    }
    
    
    public static function getPartName($part){
        $partname = str_replace(array('visual','broken','engine_','_','interior'),array('','','',' ',''),$part);
        return ucwords($partname);
    }
    
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::join('workers', 'workers.worker_id', '=', "inspections.worker_id")
              ->where('workers.group_id','=', $groupId);
        if ($tsStart){
            $query->whereBetween('ts',array($tsStart,$tsEnd));
        }else{
            $query->where('ts','<',$tsEnd);
        }
        $total = $query->get()->count();
        $areqQuery = clone $query;
        $totalActionRequired = $areqQuery->where('action_required', '=', 0)->get()->count();
        $totalActionRequiredAndFixed = $query->where('action_required', '=', 1)->get()->count();
        
        return ['total'=>$total,
                'areq' =>$totalActionRequired,
                'afix' =>$totalActionRequiredAndFixed];
    }
    
}
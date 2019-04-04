<?php

class DailySignin extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'daily_signins';
    protected $primaryKey = 'daily_signin_id';
    public $timestamps = false;
    protected $hidden = array('hazardChecklistItems');
    public $fillable = array('ts', 'first_name', 'type', 'last_name', 'created_at', 'group_id', 'company_id', 'worker_id');
    private static $properties = array('first_name', 'last_name', 'date_signout', 'date_signin');

    CONST TYPE_SIGNIN = 1;
    CONST TYPE_SIGNOUT = 0;

    public function performedBy() {
        return $this->belongsTo('Worker', 'worker_id');
    }

    public function group() {
        return $this->belongsTo('Group', 'group_id');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public function getDates() {
        return array();
    }

    public static function getForGroupForDay($group, $signDate) {
        $result = array();
        $workers = $group->getAllWorkersForGroup();
        $signins = self::where('created_at', 'like', "{$signDate}%")
                        ->where('group_id', '=', $group->group_id)->get();
        foreach ($workers as $w) {
            $element['name'] = $w->first_name . ' ' . $w->last_name;
            foreach ($signins as $k => $s) {
                if ($s->worker_id == $w->worker_id) {
                    if ($s->type == self::TYPE_SIGNIN) {
                        $element['signins'][] = $s;
                    } else {
                        $element['signouts'][] = $s;
                    }
                    $signins->forget($k);
                }
            }
            $result[] = $element;
            $element = array();
        }
        $resultNonWorkers = array();
        foreach ($signins as $s) {
            $element['name'] = $s->first_name . ' ' . $s->last_name;
            if (!array_key_exists($element['name'], $resultNonWorkers)) {
                $resultNonWorkers[$element['name']] = $element;
            }
            if ($s->type == self::TYPE_SIGNIN) {
                $resultNonWorkers[$element['name']]['signins'][] = $s;
            } else {
                $resultNonWorkers[$element['name']]['signouts'][] = $s;
            }
            $element = array();
        }
        $result = array_merge($result, $resultNonWorkers);
        return $result;
    }
    
    
    public static function getStats($groupId, $tsEnd, $timeframe){
        $tsStart = WKSSDate::getTsStart($tsEnd,$timeframe);
        $query = self::where('group_id','=', $groupId);
        if ($tsStart){
            $query->whereBetween('ts',array($tsStart,$tsEnd));
        }else{
            $query->where('ts','<',$tsEnd);
        }
        $total = $query->get()->count();
        $inQuery = clone $query;
        $in = $inQuery->where('type','=',1)->get()->count();
        $out = $query->where('type','=',0)->get()->count();
        return ['total'=> $total,
                'in' => $in,
                'out' => $out];
    }

}

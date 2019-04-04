<?php

class Group extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'groups';
    protected $primaryKey = 'group_id';
    public $timestamps = false;

    CONST GET_IDS_ONLY = 0;
    CONST GET_ALL_INFO = 1;

//           Company,    Division,   Business unit,    Group

    public function businessUnit() {
        return $this->belongsTo('BusinessUnit', 'business_unit_id');
    }

    public function clients() {
        return $this->hasMany('Client');
    }

    public static function getAllForCompany($companyId, $mode = self::GET_IDS_ONLY) {
        $result = array();
        $company = Company::find($companyId);
        foreach ($company->divisions as $division) {
            foreach ($division->businessUnits as $bu) {
                foreach ($bu->groups as $g) {
                    if ($mode == self::GET_ALL_INFO) {
                        $result[] = $g;
                    } else {
                        $result[] = $g->group_id;
                    }
                }
            }
        };
        if ($mode == self::GET_ALL_INFO) {
            return Illuminate\Database\Eloquent\Collection::make($result);
        } else {
            return $result;
        }
    }

    public function workers() {
        return $this->hasMany('Worker');
    }

    public function getAllWorkersForSignin($worker = null) {
        $workers = $this->workers()
                        ->where('disabled', '=', '0')
                        ->where('deleted_at', '=', NULL)
                        ->where('profile_completed', '=', '1')
                        ->get()->toArray();
        $result = array();
        foreach ($workers as $w) {
            if ($worker instanceOf Worker &&
                $worker->api_key != $w['api_key']) {
                    $result[] = array_only($w, array('api_key', 'first_name', 'last_name'));
            }
        }
        return $result;
    }

    public function getAllWorkersForGroup() {
        $workers = $this->workers()
                        ->where('disabled', '=', '0')->get()->all();
        return $workers;
    }

    public function isUniqueForBU() {
//           dd($this->businessUnit);
        $bu = BusinessUnit::find($this->business_unit_id);
        $existing = $bu->groups()->where('group_name', '=', $this->group_name)->get()->first();
        if (!$existing instanceOf self || $existing->group_id == $this->group_id) {
            return true;
        }
        return false;
    }

}

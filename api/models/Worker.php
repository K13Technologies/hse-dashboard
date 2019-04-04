<?php

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Worker extends Eloquent {

    use SoftDeletingTrait;
    protected $table = 'workers';
    protected $primaryKey = 'worker_id';
    
    /**
     * Disable Timestamps on table
     */
    public $timestamps = false;
    protected $guarded = array('group_id');
    protected $fillable = array('auth_token', 'api_key');
    private static $profileFieldsSet = array('first_name', 'last_name', 'birthday', 'home_phone', 'cell_phone',
        'work_phone', 'work_cell_phone', 'street', 'suite', 'city', 'state',
        'country', 'zip', 'site', 'specific_area', 'primary_trade_seniority',
        'primary_trade', 'secondary_trade', 'other_trade',
        'secondary_trade_seniority', 'other_trade_seniority',
        'next_of_kin', 'next_of_kin_relationship', 'next_of_kin_contact');
    private static $profileFieldsGet = array('first_name', 'last_name', 'birthday', 'home_phone', 'cell_phone',
        'work_phone', 'work_cell_phone', 'street', 'suite', 'city', 'state',
        'country', 'zip', 'site', 'specific_area', 'primary_trade_seniority',
        'secondary_trade_seniority', 'other_trade_seniority', 'api_key',
        'primary_trade', 'secondary_trade', 'other_trade', 'locale',
        'group_id', 'business_unit_id', 'division_id', 'company_id',
        'next_of_kin', 'next_of_kin_relationship', 'next_of_kin_contact');
    protected $hidden = array('data_via_wifi');

    /**
     * Returns the worker without organizational structures
     * @param string $authToken
     * @return Worker returns a worker if exists and NULL if it doesn't;
     */
    public static function getByAuthToken($authToken) {
        $result = Worker::where('auth_token', '=', $authToken)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }
    
    public function isDisabled() {
        return $this->disabled;
    }

    public static function getByApiKey($apiKey) {
        $result = Worker::where('api_key', '=', $apiKey)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the worker without organizational structures
     * @param string $authToken
     * @return Worker returns a worker if exists and NULL if it doesn't;
     */
    public static function getFullByToken($authToken) {
        $result = Worker::withOrganizationalStructure()->where('auth_token', '=', $authToken)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    /**
     * Returns the worker without organizational structures
     * @param string $authToken
     * @return Worker returns a worker if exists and NULL if it doesn't;
     */
    public static function withOrganizationalStructure() {
        return Worker::with(array('group', 'businessUnit', 'division', 'company'));
    }

    public function group() {
        return $this->belongsTo('Group', 'group_id');
    }

    public function businessUnit() {
        return $this->belongsTo('BusinessUnit', 'business_unit_id');
    }

    public function division() {
        return $this->belongsTo('Division', 'division_id');
    }

    public function company() {
        return $this->belongsTo('Company', 'company_id');
    }

    public function primaryTrade() {
        return $this->belongsTo('Trade', 'primary_trade_id');
    }

    public function secondaryTrade() {
        return $this->belongsTo('Trade', 'secondary_trade_id');
    }

    public function otherTrade() {
        return $this->belongsTo('Trade', 'other_trade_id');
    }
    
    public function emergencyContacts(){
         return $this->hasMany('EmergencyContact', 'worker_id');
    }

    public function tickets(){
         return $this->hasMany('Ticket', 'worker_id');
    }

    public function setGroupId($groupId) {
        $this->group_id = $groupId;
        $this->business_unit_id = $this->group->business_unit_id;
        $this->division_id = $this->businessUnit->division_id;
        $this->company_id = $this->division->company_id;
    }

    public function setFields(array $input) {
        $allEntityKeys = $this->attributesToArray();
        $entityKeys = array_only($allEntityKeys, self::$profileFieldsSet);
        foreach ($input as $key => $val) {
            if (array_key_exists($key, $entityKeys)) {
                $this->$key = $val;
            }
        }
        return $this;
    }

    public function setSettings(array $input) {
        $entityKeys = $this->hidden;
        foreach ($input as $key => $val) {
            if (in_array($key, $entityKeys)) {
                $this->$key = $val;
            }
        }
        return $this;
    }

    public static function validate() {
        
    }

    //queries and helpers

    /**
     * Returns all the tokens for the company of a worker
     * @param Worker $worker
     * @return array of tokens
     */
    public static function getTokensForCompany(Worker $worker) {
        return array_pluck(Worker::where('company_id', '=', $worker->company_id)->get()->all(), 'auth_token');
    }

    public static function getUserIdsForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            $workers = Worker::all();
        } else {
            $workers = Worker::where('company_id', '=', $admin->company_id)->get()->all();
        }
        return array_pluck($workers, 'worker_id');
    }

    public function profile() {
        $worker = $this->getFullByToken($this->auth_token);
        $profile = array_only($worker->toArray(), self::$profileFieldsGet);
        $profile = array_merge($profile, $this->getAutocompleteInfo());
        $profile['signatureList'] = $this->getSignatureIdList();
        $profile['emergencyContacts'] = $worker->emergencyContacts->toArray();
        return $profile;
    }

    /**
     * return all the autocomplete information
     */
    public function getAutocompleteInfo() {
        $workerInfo = array(
            'company_name' => $this->company->company_name,
            'division_name' => $this->division->division_name,
            'business_unit_name' => $this->businessUnit->business_unit_name,
            'group_name' => $this->group->group_name,
            'client_name' => "-",
            'client_site' => "-",
        );
        return $workerInfo;
    }

    private function getSignatureIdList() {
        $sm = new StorageManager();
        $list = $sm->getSignatureListForWorker($this);
        return $list;
    }

    public function signature() {
        $sm = new StorageManager();
        $list = $sm->getSignatureListForWorker($this);
        if ($list) {
            return $list[0];
        }
        return false;
    }

    public static function getForCompany(Admin $admin) {
        if ($admin->isAdmin()) {
            return Worker::join('companies', 'workers.company_id', '=', 'companies.company_id')
                           ->where('deleted_at','=',NULL)
                            ->orderBy('disabled')
                            ->orderBy('company_name')->get()->all();
        } else {
            return Worker::where('workers.company_id', '=', $admin->company_id)
                            ->where('deleted_at','=',NULL)
                            ->join('divisions', 'workers.division_id', '=', 'divisions.division_id')
                            ->orderBy('disabled')
                            ->orderBy('division_name')->get()->all();
        }
    }
    
    public static function getWorkerCountForCompany(Company $company) {
        return Worker::where('workers.company_id', '=', $company->company_id)
                        ->where('deleted_at','=',NULL)
                        ->get()->count();
    }
    public static function getDisabledWorkerCountForCompany(Company $company){
        return Worker::where('workers.company_id', '=', $company->company_id)
                        ->where('deleted_at','=',NULL)
                        ->where('disabled','=',1)
                        ->get()->count();
    }

    public static function generateAuthToken() {
        // Always produces a unique value
        do {
            $authToken = str_random(10);
            $worker = self::getByAuthToken($authToken);
        } while ($worker instanceof self);

        return $authToken;
    }

    public static function generateApiKey() {
        do {
            $apiKey = str_random(255);
            $worker = self::getByApiKey($apiKey);
        } while ($worker instanceof self);

        return $apiKey;
    }

}

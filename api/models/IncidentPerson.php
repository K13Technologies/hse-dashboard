<?php

class IncidentPerson extends Eloquent {

    protected $table = 'incident_persons';
    protected $primaryKey = 'incident_person_id';
    public $timestamps = false;
    public $fillable = array('incident_id', 'first_name', 'last_name', 'type', 'company');

    const EMPLOYEE_STATUS_EMPLOYEE = 0;
    const EMPLOYEE_STATUS_SUB = 1;
    const EMPLOYEE_STATUS_PRIME = 2;
    const EMPLOYEE_STATUS_BYSTANDER = 3;
    const TYPE_WORKER = 0;
    const TYPE_WITNESS = 1;
    const TYPE_3RD_PARTY = 2;

    public static $employeeStatuses = array(self::EMPLOYEE_STATUS_EMPLOYEE => 'Employee',
        self::EMPLOYEE_STATUS_SUB => 'Subcontractor',
        self::EMPLOYEE_STATUS_PRIME => 'Prime Contractor',
        self::EMPLOYEE_STATUS_BYSTANDER => 'Member of the public');
    public static $types = array(self::TYPE_WORKER => 'Worker',
        self::TYPE_WITNESS => 'Witness',
        self::TYPE_3RD_PARTY => '3rd Party');
    private static $properties = array('first_name', 'last_name', 'phone_number', 'company', 'time_on_shift',
        'time_of_incident', 'statement', 'type', 'employment_status',
        'ts_on_shift', 'ts_of_incident');

    public function getProperties() {
        return self::$properties;
    }

    public function incident() {
        return $this->belongsTo('Incident', 'incident_id');
    }

    public function setFields(array $input) {
        if ($this->type == self::TYPE_WORKER) {
            $input['ts_on_shift'] = WKSSDate::getTsFromDateWithTz($input['time_on_shift']);
            $input['ts_of_incident'] = WKSSDate::getTsFromDateWithTz($input['time_of_incident']);
        }
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)) {
                $this->$key = trim($val);
            }
        }
        return $this;
    }

    public static function savePerson($incident, $input) {
        $object = self::firstOrNew(array('first_name' => $input['first_name'],
                    'last_name' => $input['last_name'],
                    'company' => $input['company'],
                    'type' => $input['type'],
                    'incident_id' => $incident->incident_id));
        if ($object->incident_person_id) {
            return $object->incident_person_id;
        } else {
            $incidentPerson = $object;
            $incidentPerson->setFields($input);
            if ($incidentPerson->save()) {
                return $incidentPerson->incident_person_id;
            }
            return false;
        }
    }

    public function getDates() {
        return array();
    }

    public static function getEmployeeStatuses() {
        return self::$employeeStatuses;
    }

    public static function getPersonTypes() {
        return self::$types;
    }

}

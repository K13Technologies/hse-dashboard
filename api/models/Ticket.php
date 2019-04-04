<?php

class Ticket extends Eloquent {

    protected $table = 'tickets';
    protected $primaryKey = 'ticket_id';
    public $timestamps = true;
    public $fillable = array('type_name', 'issuer_organization_name', 'description', 'issued_internally',
                             'worker_id', 'created_by_admin_id', 'company_id', 'expiry_date'); //'ts'
    private static $properties = array('type_name', 'issuer_organization_name', 'description', 'issued_internally',
                             'worker_id', 'created_by_admin_id', 'company_id', 'expiry_date');


    public function getProperties() {
        return self::$properties;
    }

    public function addedBy() {
        return $this->hasOne('Admin', 'admin_id');
    }

    public function company() {
        return $this->hasOne('Company', 'company_id');
    }

    public function worker() {
        return $this->belongsTo('Worker', 'worker_id');
    }
    
    public function photos() {
        return $this->morphMany('Photo', 'imageable');
    }
    
    public function review() {
        return $this->morphOne('SafetyFormReview', 'reviewable');
    }

    public function setFields(array $input) {
        foreach ($input as $key => $val) {
            if (in_array($key, self::$properties)) {
                $this->$key = trim($val);
            }
        }
        return $this;
    }

    public static function getWithFullDetails($ticketId) {
        $ticket = Ticket::with('photos')->find($ticketId);
        $ticket->photoIds = $ticket->extractPhotoIds();

        return $ticket;
    }

    public static function getTicketsForWorker($workerId) {
        $listOftickets = self::where('worker_id', '=', $workerId)->whereRaw(ApiController::$interval)->get();
        return $listOftickets;
    }

    public function extractPhotoIds() {
        $ids = array();
        foreach ($this->photos as $p) {
            $ids[] = $p->name;
        }
        unset($this->photos);
        return $ids;
    }
}

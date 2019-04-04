<?php

class Helpline extends Eloquent {

	protected $table = 'company_helplines';
        protected $primaryKey = 'helpline_id';
        public $timestamps = false;
        
        CONST PHONE_NUMBER = 0;
        CONST RADIO_STATION = 1;
        
        public function company(){
            return $this->belongsTo('Company','company_id');
        }
        
}
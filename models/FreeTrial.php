<?php

class FreeTrial extends Eloquent {

	protected $table = 'trial_requests';
        protected $primaryKey = 'trial_id';
        public $timestamps = false;
        public $fillable = array('email','company');
        
}
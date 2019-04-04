<?php

class FAQ extends Eloquent {

	protected $table = 'faq';
        protected $primaryKey = 'faq_id';
        public $timestamps = false;
        public $fillable = array('question','answer');
        
        
        
}
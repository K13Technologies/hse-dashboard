<?php

class SafetyFormReview extends Eloquent {

    protected $table = 'form_reviews';
    protected $primaryKey = 'form_review_id';
    public $timestamps = false;
    
    public function reviewable() {
        return $this->morphTo();
    }
    
    public function signature() {
        $sm = new StorageManager;
        return $sm->getAdminSignature($this->added_by);
    }
    
    public function getDates() {
        return array();
    } 
}
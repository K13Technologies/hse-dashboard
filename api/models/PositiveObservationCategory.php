<?php

class PositiveObservationCategory extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'positive_observation_categories';
    protected $primaryKey = 'positive_observation_category_id';
    public $timestamps = false;

    public function formatString() {
        $part = str_replace('and', '&', $this->category_name);
        $this->category_name = $part;
        return $this;
    }

}

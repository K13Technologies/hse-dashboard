<?php

class FlhaTask extends Eloquent {

    protected $table = 'flha_tasks';
    protected $primaryKey = 'flha_task_id';
    public $timestamps = true;
    protected $softDelete = true;
    public $fillable = array('title', 'flha_id');
    private static $properties = array('title');

    public function getProperties() {
        return self::$properties;
    }

    public function flha() {
        return $this->belongsTo('Flha', 'flha_id');
    }

    public function hazards() {
        return $this->hasMany('FlhaTaskHazard', 'flha_task_id');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public static function getWithHazardListById($flhaTaskId) {
        $task = self::find($flhaTaskId);
        $hazardList = $task->hazards;
        $list = array();
        foreach ($hazardList as $hazard) {
            $list[] = $hazard->toArray();
        }
        $task->hazardList = $list;
        unset($task->hazards);
        return $task;
    }

    public function delete() {
        $this->hazards->each(function($h) {
            $h->delete();
        });
        return parent::delete();
    }

}

<?php

class TailgateTask extends Eloquent {

    protected $table = 'tailgate_tasks';
    protected $primaryKey = 'tailgate_task_id';
    public $timestamps = false;
    public $fillable = array('title', 'tailgate_id');
    private static $properties = array('title');

    public function getProperties() {
        return self::$properties;
    }

    public function tailgate() {
        return $this->belongsTo('Tailgate', 'tailgate_id');
    }

    public function hazards() {
        return $this->hasMany('TailgateTaskHazard', 'tailgate_task_id');
    }

    public function setFields(array $input) {
        foreach (self::$properties as $key) {
            if (array_key_exists($key, $input)) {
                $this->$key = $input[$key];
            }
        }
        return $this;
    }

    public static function getWithHazardListById($tailgateTaskId) {
        $task = self::find($tailgateTaskId);
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

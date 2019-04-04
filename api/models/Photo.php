<?php

class Photo extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'photos';
    protected $primaryKey = 'photo_id';
    public $timestamps = false;
    public $fillable = array('original_name', 'imageable_type', 'imageable_id', 'worker_id','used_for');

    public function imageable() {
        return $this->morphTo();
    }

    public static function getPhotoByPhotoName($photoName) {
        $result = self::where('name', '=', $photoName)->get();
        if ($result->isEmpty()) {
            return NULL;
        }
        return $result->first();
    }

    public static function generatePhotoName() {
        do {
            $photoName = uniqid(true);
            $photo = self::getPhotoByPhotoName($photoName);
        } while ($photo instanceof self);

        return $photoName;
    }

    public static function generic($content) {
        return URL::to("image/$content");
    }

}

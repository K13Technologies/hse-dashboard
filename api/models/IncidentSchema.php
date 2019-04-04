<?php

class IncidentSchema extends Eloquent {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'incident_schemas';
    protected $primaryKey = 'incident_schema_id';
    public $timestamps = false;

    const TYPE_TRUCK = 1;
    const TYPE_TRAILER = 3;
    const TYPE_BODY = 2;
    
    const FILE_BODY = 'human';
    const FILE_TRAILER = 'trailer';
    const FILE_TRUCK = 'truck';
    
    const SIZE_IOS_NON_RETINA = 'ios_non_retina';
    const SIZE_IOS_RETINA = 'ios_retina';

    public $sizes = array(self::SIZE_IOS_NON_RETINA, self::SIZE_IOS_RETINA);

    public function parts() {
        return $this->hasMany('IncidentSchemaPart', 'incident_schema_id');
    }

    public static function createFromPlist($path, $type) {
        $images = $path['images'];
        $parts = $path['parts'];

        foreach ($images as $key => $val) {
            $schema = new self();
            $schema->key = $key;
            $schema->type = $type;
            $schema->save();
            foreach ($parts as $p => $v) {
                $part = new IncidentSchemaPart();
                $part->key = $p;
                $coords = explode('/', $v);
                $part->description = ucfirst(str_replace('_', ' ', $p));
                $part['x1'] = trim($coords[0]);
                $part['y1'] = trim($coords[1]);
                $part['x2'] = trim($coords[2]);
                $part['y2'] = trim($coords[3]);
                $part->incident_schema_id = $schema->incident_schema_id;
                $part->save();
            }
        }
    }

    public static function getWithParts() {
        $schemas = self::with('parts')->get();
        $result = array();
        foreach ($schemas as $s) {
            $sizes = $s->generateURLs();
            if ($s->type == self::TYPE_BODY) {
                $parts = array();
                foreach ($s->parts as $p) {
                    $partType = head(explode('_', $p->key));
                    $parts[$partType][] = $p->toArray();
                }
                unset($s->parts);
                $s->parts = $parts;
            }
            $s = $s->toArray();
            $s['sizes'] = $sizes;
            $result[] = $s;
        }
        return $result;
    }

    private function generateURLs() {
        $dims = new stdClass();
        foreach ($this->sizes as $size) {
            $dims->$size = URL::to("api/image/incident-schema/{$this->incident_schema_id}/{$size}");
        }
        return $dims;
    }

    public static function getType($f) {
        if (starts_with($f, self::FILE_TRUCK)) {
            return self::TYPE_TRUCK;
        }
        if (starts_with($f, self::FILE_BODY)) {
            return self::TYPE_BODY;
        }
        if (starts_with($f, self::FILE_TRAILER)) {
            return self::TYPE_TRAILER;
        }
        return 0;
    }

}

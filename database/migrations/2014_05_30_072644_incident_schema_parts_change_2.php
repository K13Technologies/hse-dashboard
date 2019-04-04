<?php

use Illuminate\Database\Migrations\Migration;

class IncidentSchemaPartsChange2 extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $undercarriage = 21;
        $undercarriageRight = 22;

        $trailerUndercarriage = 24;
        $trailerUndercarriageRight = 25;

        IncidentPartStatement::where('incident_schema_part_id', '=', $undercarriageRight)->update(array('incident_schema_part_id' => $undercarriage));
        IncidentSchemaPart::where('incident_schema_part_id', '=', $undercarriage)->update(array('key' => 'undercarriage', 'description' => 'Undercarriage'));
        IncidentSchemaPart::destroy($undercarriageRight);

        IncidentPartStatement::where('incident_schema_part_id', '=', $trailerUndercarriageRight)->update(array('incident_schema_part_id' => $trailerUndercarriage));
        IncidentSchemaPart::where('incident_schema_part_id', '=', $trailerUndercarriage)->update(array('key' => 'trailer_undercarriage', 'description' => 'Trailer Undercarriage'));
        IncidentSchemaPart::destroy($trailerUndercarriageRight);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        
    }

}

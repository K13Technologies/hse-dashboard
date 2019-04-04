<?php

use Illuminate\Database\Migrations\Migration;

class IncidentSchemaPartsChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            DB::statement("UPDATE incident_schema_parts SET description = REPLACE(`key`, '_', ' ')");
            DB::statement("UPDATE incident_schema_parts SET description = CONCAT(UPPER(SUBSTRING(description, 1, 1)), LOWER(SUBSTRING(description FROM 2)))");
	
            HazardChecklistItem::add('Reviewed MSDS documentation', 12);
        }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            HazardChecklistItem::remove('Reviewed MSDS documentation');
	}

}
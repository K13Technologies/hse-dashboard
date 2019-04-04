<?php

use Illuminate\Database\Migrations\Migration;

class InspectionFieldChanges2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('inspections',function($table){
                    $table->renameColumn('engine_seatbelts', 'interior_seatbelts');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('inspections', function($table){
                    $table->renameColumn('interior_seatbelts', 'engine_seatbelts');
                });
	}

}
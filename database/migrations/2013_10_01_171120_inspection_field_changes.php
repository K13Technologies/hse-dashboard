<?php

use Illuminate\Database\Migrations\Migration;

class InspectionFieldChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('inspections',function($table){
                    $table->string('interior_booster_cables',100);
                    $table->renameColumn('iterior_warning_triangles', 'interior_warning_triangles');
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
                    $table->dropColumn('interior_booster_cables');
                    $table->renameColumn('interior_warning_triangles', 'iterior_warning_triangles');
                });
	}

}
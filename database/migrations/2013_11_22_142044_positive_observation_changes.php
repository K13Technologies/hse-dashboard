<?php

use Illuminate\Database\Migrations\Migration;

class PositiveObservationChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('positive_observations', function($table){
                    $table->dropColumn('corrective_action', 'corrective_action_applied', 'corrective_action_implementation');
                    $table->boolean('is_positive_observation');
                    $table->string('is_po_details', 400);
                    $table->boolean('correct_on_site');
                });
	}
        

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('positive_observations',function($table){
                    $table->dropColumn('is_positive_observation', 'is_po_details', 'correct_on_site');
                    $table->string('corrective_action', 400);
                    $table->boolean('corrective_action_applied');
                    $table->string('corrective_action_implementation', 400);
                });
	}

}
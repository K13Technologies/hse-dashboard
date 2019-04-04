<?php

use Illuminate\Database\Migrations\Migration;

class AddCreatedAtForJourney extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('journeys',function($table){
                    $table->datetime('created_at')->after('distance_estimate');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('journeys',function($table){
                    $table->dropColumn('created_at');
                });
	}

}
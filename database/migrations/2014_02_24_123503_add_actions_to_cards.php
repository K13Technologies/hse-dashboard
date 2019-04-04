<?php

use Illuminate\Database\Migrations\Migration;

class AddActionsToCards extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('hazards',function($table){
                    $table->string('action', 500);
                    $table->date('completed_on')->nullable()->default(NULL);
                });
                Schema::table('positive_observations',function($table){
                    $table->string('action', 500);
                    $table->date('completed_on')->nullable()->default(NULL);
                });
                Schema::table('near_misses',function($table){
                    $table->string('action', 500);
                    $table->date('completed_on')->nullable()->default(NULL);
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('hazards',function($table){
                    $table->dropColumn('action');
                    $table->dropColumn('completed_on');
                });
                Schema::table('positive_observations',function($table){
                    $table->dropColumn('action');
                    $table->dropColumn('completed_on');
                });
                Schema::table('near_misses',function($table){
                    $table->dropColumn('action');
                    $table->dropColumn('completed_on');
                });
	}

}
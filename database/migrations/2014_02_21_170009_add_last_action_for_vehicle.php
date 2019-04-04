<?php

use Illuminate\Database\Migrations\Migration;

class AddLastActionForVehicle extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('vehicles',function($table){
                    $table->date('last_action_date')->after('updated_at')->nullable()->default(NULL);
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('vehicles',function($table){
                    $table->dropColumn('last_action_date');
                });
	}

}
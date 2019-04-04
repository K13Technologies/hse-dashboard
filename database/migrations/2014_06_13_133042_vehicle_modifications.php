<?php

use Illuminate\Database\Migrations\Migration;

class VehicleModifications extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('vehicles',function($table){
                $table->string('make',100);
                $table->string('model',50);
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
                $table->dropColumn('make');
                $table->dropColumn('model');
            });
	}

}
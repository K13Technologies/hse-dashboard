<?php

use Illuminate\Database\Migrations\Migration;

class RemoveVehicleNumberUnique extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('vehicles',function($table){
                $table->dropUnique('vehicles_vehicle_number_unique');
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
               $table->unique('vehicle_number');
            });
	}

}
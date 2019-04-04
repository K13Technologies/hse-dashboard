<?php

use Illuminate\Database\Migrations\Migration;

class MakeVehiclesUniquePerCompany extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('vehicles', function($table)
            {
                $table->dropUnique('vehicles_license_plate_unique');
                $table->unique(array('license_plate','company_id'));
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('vehicles', function($table)
            {
                $table->dropUnique('vehicles_license_plate_company_id_unique');
                $table->unique('license_plate');
            });
	}

}
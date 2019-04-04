<?php

use Illuminate\Database\Migrations\Migration;

class ChangeEstimateDistance extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                DB::statement("ALTER TABLE  `journeys` CHANGE  `distance_estimate`  `distance_estimate` INT NOT NULL");
	}
        
        
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE  `journeys` CHANGE  `distance_estimate`  `distance_estimate` DECIMAL(5,2) NOT NULL");
	}

}
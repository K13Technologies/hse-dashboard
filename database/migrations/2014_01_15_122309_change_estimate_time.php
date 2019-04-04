<?php

use Illuminate\Database\Migrations\Migration;

class ChangeEstimateTime extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                DB::statement("ALTER TABLE  `journeys` CHANGE  `time_estimate`  `time_estimate` FLOAT NOT NULL");
	}
        
        
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE  `journeys` CHANGE  `time_estimate`  `time_estimate` DECIMAL(5,2) NOT NULL");
	}


}
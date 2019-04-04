<?php

use Illuminate\Database\Migrations\Migration;

class ChangeCheckinTime extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            DB::statement("ALTER TABLE  `journey_checkins_v2` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            DB::statement("ALTER TABLE  `journey_checkins_v2` CHANGE  `created_at`  `created_at` DATETIME NOT NULL");
	}

}
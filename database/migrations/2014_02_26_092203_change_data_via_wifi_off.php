<?php

use Illuminate\Database\Migrations\Migration;

class ChangeDataViaWifiOff extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            DB::statement("ALTER TABLE  `workers` ALTER  `data_via_wifi`  SET DEFAULT 0");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            DB::statement("ALTER TABLE  `workers` ALTER  `data_via_wifi`  SET DEFAULT 1");
	}

}
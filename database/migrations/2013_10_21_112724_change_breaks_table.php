<?php

use Illuminate\Database\Migrations\Migration;

class ChangeBreaksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::rename('flha_signoff_break','flha_signoff_breaks');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::rename('flha_signoff_breaks','flha_signoff_break');
	}

}
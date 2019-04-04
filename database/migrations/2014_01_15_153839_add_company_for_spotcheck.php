<?php

use Illuminate\Database\Migrations\Migration;

class AddCompanyForSpotcheck extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('flha_spotchecks',function($table){
                    $table->string('company', 100)->after('position');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('flha_spotchecks',function($table){
                    $table->dropColumn('company');
                });
	}

}
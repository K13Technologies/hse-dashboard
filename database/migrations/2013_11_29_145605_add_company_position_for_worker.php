<?php

use Illuminate\Database\Migrations\Migration;

class AddCompanyPositionForWorker extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('flha_signoff_workers', function($table){
                    $table->string('company',100)->after('last_name');
                    $table->string('position', 100)->after('company');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('flha_signoff_workers', function($table){
                    $table->dropColumn('company');
                    $table->dropColumn('position');
                });
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class FlhaSafetyRepNumber extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('flhas',function($table){

                    $table->renameColumn('site_emergency_number', 'supervisor_number');
                    $table->string('safety_rep',100)->after('safety_rep_number');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('flhas',function($table){

                    $table->renameColumn('supervisor_number', 'site_emergency_number');
                    $table->dropColumn('safety_rep');
                });
	}

}
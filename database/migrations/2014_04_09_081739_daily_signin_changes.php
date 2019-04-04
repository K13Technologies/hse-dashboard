<?php

use Illuminate\Database\Migrations\Migration;

class DailySigninChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('daily_signins',function($table){
                $table->renameColumn('name', 'first_name');
                $table->string('last_name',200);
                $table->decimal('latitude',18,12);
                $table->decimal('longitude',18,12);
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('daily_signins',function($table){
                $table->renameColumn('first_name', 'name');
                $table->dropColumn('last_name');
                $table->dropColumn('latitude');
                $table->dropColumn('longitude');
            });
	}

}
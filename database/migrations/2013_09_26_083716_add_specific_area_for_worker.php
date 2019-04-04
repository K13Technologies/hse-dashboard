<?php

use Illuminate\Database\Migrations\Migration;

class AddSpecificAreaForWorker extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('workers',function($table){
                    $table->string('specific_area',100);
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('workers', function($table){
                    $table->dropColumn('specific_area');
                });
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class ChangeWipersToVisual extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('inspections',function($table){
                    $table->renameColumn('engine_wippers', 'visual_wipers');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::table('inspections',function($table){
                    $table->renameColumn('visual_wipers', 'engine_wippers');
                });
	}

}
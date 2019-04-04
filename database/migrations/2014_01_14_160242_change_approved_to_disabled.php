<?php

use Illuminate\Database\Migrations\Migration;

class ChangeApprovedToDisabled extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('workers',function($table){
                    $table->renameColumn('approved', 'disabled');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		  Schema::table('workers',function($table){
                    $table->renameColumn('disabled', 'approved');
                });
	}

}
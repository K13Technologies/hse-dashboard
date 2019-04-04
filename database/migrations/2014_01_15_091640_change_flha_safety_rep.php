<?php

use Illuminate\Database\Migrations\Migration;

class ChangeFlhaSafetyRep extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('flhas',function($table){
                   $table->renameColumn('safety_rep', 'safety_rep_name');
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
                    $table->renameColumn('safety_rep_name', 'safety_rep');
                });
	}

}
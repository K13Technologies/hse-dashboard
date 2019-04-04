<?php

use Illuminate\Database\Migrations\Migration;

class HazardFieldChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
        public function up()
	{
                Schema::table('hazards',function($table){
                    $table->string('wellpad',100)->after('lsd');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('hazards', function($table){
                    $table->dropColumn('wellpad');
                });
	}

}
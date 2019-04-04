<?php

use Illuminate\Database\Migrations\Migration;

class AddInspectionNullFlag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('inspections',function($table){
                $table->boolean('has_nulls')->default(false);
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
                $table->dropColumn('has_nulls');
            });
	}

}
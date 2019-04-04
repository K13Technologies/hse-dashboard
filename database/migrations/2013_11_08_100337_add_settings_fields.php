<?php

use Illuminate\Database\Migrations\Migration;

class AddSettingsFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('workers',function($table){
                    $table->boolean('data_via_wifi')->default(1);
                    $table->string('locale',10)->default('CA');
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
                    $table->dropColumn('data_via_wifi');
                    $table->dropColumn('locale');
                });
	}

}
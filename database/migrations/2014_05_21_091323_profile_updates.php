<?php

use Illuminate\Database\Migrations\Migration;

class ProfileUpdates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('workers',function($table){
                    $table->string('next_of_kin',100)->after('site');
                    $table->string('next_of_kin_relationship',100)->after('next_of_kin');
                    $table->string('next_of_kin_contact',100)->after('next_of_kin_relationship');
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
                    $table->dropColumn('next_of_kin');
                    $table->dropColumn('next_of_kin_relationship');
                    $table->dropColumn('next_of_kin_contact');
                });
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class ChangeOccupationFields extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('workers',function($table){
                $table->string('primary_trade',100)->after('site');
                $table->string('secondary_trade',100)->after('primary_trade');
                $table->string('other_trade',100)->after('secondary_trade');
                $table->dropForeign('workers_primary_trade_id_foreign');
                $table->dropColumn('primary_trade_id');
                $table->dropForeign('workers_secondary_trade_id_foreign');
                $table->dropColumn('secondary_trade_id');
                $table->dropForeign('workers_other_trade_id_foreign');
                $table->dropColumn('other_trade_id');
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
                $table->dropColumn('primary_trade');
                $table->dropColumn('secondary_trade');
                $table->dropColumn('other_trade');
                $table->integer('primary_trade_id')->nullable()->unsigned()->default(NULL);
                $table->integer('secondary_trade_id')->nullable()->unsigned()->default(NULL);
                $table->integer('other_trade_id')->nullable()->unsigned()->default(NULL);
            });
	}

}
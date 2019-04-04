<?php

use Illuminate\Database\Migrations\Migration;

class AssignVehicleToCompany extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('vehicles',function($table){
                $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                //set up foreign keys
                $table->foreign('company_id')->references('company_id')->on('companies');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('vehicles',function($table){
                $table->dropForeign('vehicles_company_id_foreign');
                $table->dropColumn('company_id');
            });
	}

}
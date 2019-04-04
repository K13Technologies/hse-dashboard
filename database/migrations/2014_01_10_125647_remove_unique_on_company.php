<?php

use Illuminate\Database\Migrations\Migration;

class RemoveUniqueOnCompany extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('divisions', function($table)
            {
                $table->dropUnique('company_id');
            });
            Schema::table('business_units', function($table)
            {
                $table->dropUnique('division_id');
            });
            Schema::table('groups', function($table)
            {
                $table->dropUnique('business_unit_id');
            });
               
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('divisions', function($table)
            {
                $table->unique('division_name','company_id');
            });
            Schema::table('business_units', function($table)
            {
                $table->unique('business_unit_name','division_id');
            });
            Schema::table('groups', function($table)
            {
                  $table->unique('group_name','business_unit_id');
            });
               
              
	}

}
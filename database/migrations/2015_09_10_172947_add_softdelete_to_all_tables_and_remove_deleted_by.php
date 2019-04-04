<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftdeleteToAllTablesAndRemoveDeletedBy extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('workers',function($table){
			// The reason why this check is required is because for this particular set of changes, they were done manually instead of through migrations
			// So we never want this migration to fail 
		    if (!Schema::hasColumn('workers', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    // Removing this functionality because it was manually done before and is not necessary right now
		    // Audit capability will likely be added in the future, rendering this column redundant 
		    if (Schema::hasColumn('workers', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		// ======= START safety category tables ==========
		Schema::table('near_misses',function($table){
		    if (!Schema::hasColumn('near_misses', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('near_misses', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('hazards',function($table){
		    if (!Schema::hasColumn('hazards', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('hazards', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('positive_observations',function($table){
		    if (!Schema::hasColumn('positive_observations', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('positive_observations', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('flhas',function($table){
		    if (!Schema::hasColumn('flhas', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('flhas', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('tailgates',function($table){
		    if (!Schema::hasColumn('tailgates', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('tailgates', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('incidents',function($table){
		    if (!Schema::hasColumn('incidents', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('incidents', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		// ========= END safety category tables =========


		Schema::table('vehicles',function($table){
		    if (!Schema::hasColumn('vehicles', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('vehicles', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});


		Schema::table('journeys_v2',function($table){
		    if (!Schema::hasColumn('journeys_v2', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('journeys_v2', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});

		Schema::table('admins',function($table){
		    if (!Schema::hasColumn('admins', 'deleted_at')) {
		        $table->softDeletes();
		    }

		    if (Schema::hasColumn('admins', 'deleted_by_admin_id')) {
		        $table->dropColumn('deleted_by_admin_id');
		    }
		});	
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// For the deleted_by_admin_id columns in the 'up' method, we have no down for them, because we just want them gone, period. 
		// They were added manually without migrations and are not needed. 

		Schema::table('workers',function($table){
			// The reason why this check is required is because for this particular set of changes, they were done manually instead of through migrations
			// So we never want this migration to fail 
		    if (Schema::hasColumn('workers', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		// ======= START safety category tables ==========
		Schema::table('near_misses',function($table){
		    if (Schema::hasColumn('near_misses', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('hazards',function($table){
		    if (Schema::hasColumn('hazards', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('positive_observations',function($table){
		    if (Schema::hasColumn('positive_observations', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('flhas',function($table){
		    if (Schema::hasColumn('flhas', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('tailgates',function($table){
		    if (Schema::hasColumn('tailgates', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('incidents',function($table){
		    if (Schema::hasColumn('incidents', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		// ========= END safety category tables =========


		Schema::table('vehicles',function($table){
		    if (Schema::hasColumn('vehicles', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('journeys_v2',function($table){
		    if (Schema::hasColumn('journeys_v2', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});

		Schema::table('admins',function($table){
		    if (Schema::hasColumn('admins', 'deleted_at')) {
		        $table->dropSoftDeletes();
		    }
		});	
	}
}
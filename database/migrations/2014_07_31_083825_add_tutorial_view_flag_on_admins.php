<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTutorialViewFlagOnAdmins extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('admins', function(Blueprint $table)
            {
                $table->boolean('show_tutorial')->default(true);
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('admins', function(Blueprint $table)
            {
                $table->dropColumn( 'show_tutorial');
            });
	}

}

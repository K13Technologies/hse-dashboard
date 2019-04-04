<?php

use Illuminate\Database\Migrations\Migration;

class AddPhotoOriginalName extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::table('photos',function($table){
                    $table->string('original_name', 500);
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		 Schema::table('photos',function($table){
                    $table->dropColumn('original_name');
                });
	}

}
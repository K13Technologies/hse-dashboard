<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSafetyManualRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('safety_manual_revisions', function(Blueprint $table)
		{
			$table->increments('revision_id');
			$table->integer('safety_manual_id')->unsigned();
			$table->integer('admin_id')->unsigned();
			$table->integer('major_version_number');
			$table->integer('minor_version_number');
			$table->longText('revision_description');
			$table->string('ip_address');
			$table->timestamps();

			// Foreign keys
			$table->foreign('safety_manual_id')
				  ->references('safety_manual_id')
				  ->on('safety_manuals')
				  ->onDelete('cascade');
			$table->foreign('admin_id')
				  ->references('admin_id')->on('admins');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('safety_manual_revisions');
	}

}

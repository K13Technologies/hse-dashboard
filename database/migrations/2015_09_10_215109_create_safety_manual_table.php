<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSafetyManualTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('safety_manuals', function(Blueprint $table)
		{
			$table->increments('safety_manual_id');
			$table->integer('company_id')->unsigned();
			$table->integer('major_version_number');
			$table->integer('minor_version_number');
			$table->longText('manual_content');
			$table->timestamps();

			// Foreign keys
			$table->foreign('company_id')
			      ->references('company_id')->on('companies')
			      ->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('safety_manuals');
	}

}

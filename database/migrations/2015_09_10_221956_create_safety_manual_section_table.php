<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSafetyManualSectionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('safety_manual_sections', function(Blueprint $table)
		{
			$table->increments('section_id');
			$table->integer('safety_manual_id')->unsigned();
			$table->string('section_title', 1500);
			$table->tinyInteger('is_SJP'); // Safe Job Procedures -- this is a special section type that needs to be tracked
			$table->tinyInteger('is_SWP'); // Safe Work Practice -- this is a special section type that needs to be tracked
			$table->integer('section_order_number'); // Used in the future in case we add re-ordering of sections as a feature
			$table->text('section_description')->nullable();
			$table->timestamps();

			// Foreign keys
			$table->foreign('safety_manual_id')
			      ->references('safety_manual_id')->on('safety_manuals')
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
		Schema::dropIfExists('safety_manual_sections');
	}

}

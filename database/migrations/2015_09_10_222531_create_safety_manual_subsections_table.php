<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSafetyManualSubsectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('safety_manual_subsections', function(Blueprint $table)
		{
			$table->increments('subsection_id');
			$table->integer('safety_manual_id')->unsigned();
			$table->integer('section_id')->unsigned();
			$table->integer('subsection_order_number'); // Used in the future in case we add re-ordering of sections as a feature
			$table->string('subsection_title', 1500);
			$table->longtext('subsection_content')->nullable();
			$table->longtext('subsection_mobile_content')->nullable();
			$table->timestamps();

			$table->foreign('section_id')
			      ->references('section_id')->on('safety_manual_sections')
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
		//$table->dropForeign('section_id');
		Schema::dropIfExists('safety_manual_subsections');
	}

}

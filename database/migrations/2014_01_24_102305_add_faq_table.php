<?php

use Illuminate\Database\Migrations\Migration;

class AddFaqTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('faq',function($table){
                    $table->increments('faq_id');
                    $table->string('question', 300);
                    $table->text('answer');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('faq');
	}

}
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFormReviewComponent extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_reviews',function($table){
                    $table->increments('form_review_id');
                    $table->string('reviewer_name', 200);
                    $table->string('created_at', 100);
                    $table->integer('ts')->unsigned();
                    $table->integer('reviewable_id')->unsigned();
                    $table->string('reviewable_type',50);
                    $table->integer('added_by')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                });
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('form_reviews');
	}

}

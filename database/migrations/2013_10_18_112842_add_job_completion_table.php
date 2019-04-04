<?php

use Illuminate\Database\Migrations\Migration;

class AddJobCompletionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('job_completions',function($table){
                    $table->increments('job_completion_id');
                    $table->boolean('permit_closed');
                    $table->string('permit_closed_description',400);
                    $table->boolean('hazard_remaining');
                    $table->string('hazard_remaining_description',400);
                    $table->boolean('flagging_removed');
                    $table->string('flagging_removed_description',400);
                    $table->boolean('incidents_reported');
                    $table->string('incidents_reported_description',400);
                    $table->boolean('concerns');
                    $table->string('concerns_description',400);
                    $table->boolean('equipment_removed');
                    $table->string('equipment_removed_description',400);
                    $table->integer('completioner_id')->unsigned();
                    $table->string('completioner_type',50);
                    $table->timestamps();
//                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
//                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('job_completion');
	}

}
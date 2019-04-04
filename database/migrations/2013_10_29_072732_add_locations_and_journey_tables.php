<?php

use Illuminate\Database\Migrations\Migration;

class AddLocationsAndJourneyTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('journey_locations',function($table){
                    $table->increments('location_id');
                    $table->string('title', 100);
                    $table->string('company', 100);
                    $table->string('street', 200);
                    $table->string('suite', 200);
                    $table->string('city', 100);
                    $table->string('zip', 8);
                    $table->string('state', 100);
                    $table->string('specific_location', 100);
                    $table->string('wellpad',100);
                    $table->string('lsd', 30);
                    $table->smallInteger('type');
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                });
                    
                Schema::create('journeys',function($table){
                    $table->increments('journey_id');
                    $table->decimal('time_estimate',5,2);
                    $table->decimal('distance_estimate',5,2);
                    $table->datetime('started_at')->nullable();
                    $table->datetime('finished_at')->nullable()->default(NULL);
                    $table->integer('location_from')->nullable()->unsigned()->default(NULL);
                    $table->integer('location_to')->nullable()->unsigned()->default(NULL);
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                    $table->foreign('location_from')->references('location_id')->on('journey_locations');
                    $table->foreign('location_to')->references('location_id')->on('journey_locations');
                });
                
                Schema::create('journey_checkins',function($table){
                    $table->increments('checkin_id');
                    $table->decimal('latitude',18,12);
                    $table->decimal('longitude',18,12);
                    $table->datetime('created_at');
                    $table->boolean('is_active');
                    $table->boolean('journey_finished');
                    $table->boolean('is_worker_added')->default(1);
                    $table->integer('journey_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                    $table->foreign('journey_id')->references('journey_id')->on('journeys');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('journey_checkins');
                Schema::drop('journeys');
                Schema::drop('journey_locations');
	}

}
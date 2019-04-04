<?php

use Illuminate\Database\Migrations\Migration;

class JourneyManagement2 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::table('journey_locations',function($table){
                $table->decimal('latitude',18,12)->nullable()->default(NULL);
                $table->decimal('longitude',18,12)->nullable()->default(NULL);
            });
            
            Schema::create('journeys_v2',function($table){
                $table->increments('journey_id');
                $table->string('created_at',50)->nullable();
                $table->string('started_at',50)->nullable();
                $table->string('finished_at',50)->nullable()->default(NULL);
                $table->integer('ts_created');
                $table->integer('ts_started');
                $table->integer('ts_finished');
                $table->integer('starting_point')->nullable()->unsigned()->default(NULL);
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                //set up foreign keys
                $table->foreign('worker_id')->references('worker_id')->on('workers');
                $table->foreign('starting_point')->references('location_id')->on('journey_locations');
            });
            
            Schema::create('journeys_v2_has_endpoints',function($table){
                $table->increments('journey_endpoint_id');
                $table->integer('journey_id')->nullable()->unsigned()->default(NULL);
                $table->integer('location_id')->nullable()->unsigned()->default(NULL);
                
                $table->string('arrived',50)->nullable()->default(NULL);
                $table->integer('ts_arrived');
                
                //set up foreign keys
                $table->foreign('journey_id')->references('journey_id')->on('journeys_v2')->onDelete('cascade');
                $table->foreign('location_id')->references('location_id')->on('journey_locations')->onDelete('cascade');
            });
                
            Schema::create('journey_checkins_v2',function($table){
                $table->increments('checkin_id');
                $table->decimal('latitude',18,12);
                $table->decimal('longitude',18,12);
                $table->datetime('created_at');
                $table->integer('ts');
                $table->boolean('is_active');
                $table->boolean('is_worker_added')->default(1);
                $table->integer('journey_id')->nullable()->unsigned()->default(NULL);
                //set up foreign keys
                $table->foreign('journey_id')->references('journey_id')->on('journeys_v2');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('journey_locations',function($table){
                $table->dropColumn('latitude');
                $table->dropColumn('longitude');
            });
            
            Schema::drop('journey_checkins_v2');
            Schema::drop('journeys_v2_has_endpoints');
            Schema::drop('journeys_v2');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class AddIncident extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            $toRename = array('Construction - Facility'=>'Construction - Facilities');
            $toAdd = array('Construction');
            
            foreach ($toRename as $from => $to ){
                HazardActivity::rename($from, $to);
                PositiveObservationActivity::rename($from, $to);
            }
            foreach ($toAdd as $activityName){
                PositiveObservationActivity::add($activityName);
                HazardActivity::add($activityName);
            }
            
            Schema::create('incident_types',function($table){
                $table->increments('incident_type_id');
                $table->string('type_name',150);
            });
            
            Schema::create('activities',function($table){
                $table->increments('activity_id');
                $table->string('activity_name',150);
                $table->boolean('hazard');
                $table->boolean('po');
                $table->boolean('near_miss');
                $table->boolean('incident');
            });
            
            Schema::create('incidents', function($table) {
                $table->increments('incident_id');
                $table->string('title', 100);
                $table->string('lsd', 50);
                $table->string('utm', 400);
                $table->string('source_receiver_line', 100);
                $table->decimal('latitude',18,12);
                $table->decimal('longitude',18,12);
                $table->string('location', 400);
                $table->string('specific_area', 400);
                $table->string('road', 400);
                $table->string('description', 1000);
                $table->string('root_cause',1000);
                $table->string('immediate_action', 1000);
                $table->string('corrective_action', 1000);
                $table->boolean('corrective_action_applied');
                $table->string('corrective_action_implementation', 1000);
                $table->string('action', 500);
                $table->date('completed_on')->nullable()->default(NULL);
                $table->string('created_at',50);
                $table->integer('ts');
                //set up foreign keys
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                $table->foreign('worker_id')->references('worker_id')->on('workers');
            });
            
            Schema::create('incident_has_activities',function($table){
                $table->integer('incident_id')->unsigned();
                $table->integer('activity_id')->unsigned();
                $table->unique(array('incident_id','activity_id'));
                $table->foreign('incident_id')->references('incident_id')->on('incidents')->onDelete('cascade');
            });
            
            Schema::create('incident_has_types',function($table){
                $table->increments('incident_has_type_id');
                $table->integer('incident_id')->unsigned();
                $table->integer('incident_type_id')->unsigned();
                $table->unique(array('incident_id','incident_type_id'));
                $table->foreign('incident_id')->references('incident_id')->on('incidents')->onDelete('cascade');
                $table->foreign('incident_type_id')->references('incident_type_id')->on('incident_types')->onDelete('cascade');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{       
            $toRename = array('Construction - Facility'=>'Construction - Facilities');
            $toAdd = array('Construction');
            
            foreach ($toRename as $from => $to ){
                HazardActivity::rename($from, $to);
                PositiveObservationActivity::rename($from, $to);
            }
            foreach ($toAdd as $activityName){
                PositiveObservationActivity::add($activityName);
                HazardActivity::add($activityName);
            }
            
            Schema::drop('incident_has_types');
            Schema::drop('incident_has_activities');
            Schema::drop('incidents');
            Schema::drop('incident_types');
            Schema::drop('activities');
            
	}

}
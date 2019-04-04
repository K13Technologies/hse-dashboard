<?php

use Illuminate\Database\Migrations\Migration;

class AddPositiveObservationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('positive_observation_activities',function($table){
                    $table->increments('positive_observation_activity_id');
                    $table->string('activity_name', 100);
                });
                
                Schema::create('positive_observation_categories',function($table){
                    $table->increments('positive_observation_category_id');
                    $table->string('category_name', 100);
                });
                
                Schema::create('positive_observations',function($table){
                    $table->increments('positive_observation_id');
                    $table->string('title', 100);
                    $table->string('site', 100);
                    $table->string('specific_location', 100);
                    $table->string('lsd', 30);
                    $table->string('wellpad', 100);
                    $table->string('road', 100);
                    $table->string('description', 400);
                    $table->string('task_1_title', 100);
                    $table->string('task_1_description', 400);
                    $table->string('task_2_title', 100);
                    $table->string('task_2_description', 400);
                    $table->string('task_3_title', 100);
                    $table->string('task_3_description', 400);
                    $table->string('corrective_action', 400);
                    $table->boolean('corrective_action_applied');
                    $table->string('corrective_action_implementation', 400);
                    $table->string('comment', 400);
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    $table->integer('positive_observation_activity_id')->nullable()->unsigned()->default(NULL);
                    $table->timestamps();

                    //set up foreign keys
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                    $table->foreign('positive_observation_activity_id')->references('positive_observation_activity_id')->on('positive_observation_activities');
                    
                });
                
                Schema::create('po_has_po_category',function($table){
                    $table->integer('po_id')->unsigned();
                    $table->integer('po_category_id')->unsigned();
                    $table->unique(array('po_id','po_category_id'));
                    $table->foreign('po_id')->references('positive_observation_id')->on('positive_observations')->onDelete('cascade');
                    $table->foreign('po_category_id')->references('positive_observation_category_id')->on('positive_observation_categories');
                    
                });
                
                Schema::create('positive_observation_persons',function($table){
                    $table->increments('person_id');
                    $table->string('name',100);
                    $table->string('company',100);
                    $table->integer('positive_observation_id')->nullable()->unsigned()->default(NULL);
                  
                    $table->foreign('positive_observation_id')->references('positive_observation_id')->on('positive_observations')->onDelete('cascade');
                    
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('positive_observation_persons');
                Schema::drop('po_has_po_category');
                Schema::drop('positive_observations');
                Schema::drop('positive_observation_categories');
                Schema::drop('positive_observation_activities');
	}

}
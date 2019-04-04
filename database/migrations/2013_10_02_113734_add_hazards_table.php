<?php

use Illuminate\Database\Migrations\Migration;

class AddHazardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('hazard_activities',function($table){
                    $table->increments('hazard_activity_id');
                    $table->string('activity_name', 100);
                });
                
                Schema::create('hazard_categories',function($table){
                    $table->increments('hazard_category_id');
                    $table->string('category_name', 100);
                });
                
                Schema::create('hazards',function($table){
                    $table->increments('hazard_id');
                    $table->string('title', 100);
                    $table->string('site', 100);
                    $table->string('specific_location', 100);
                    $table->string('lsd', 30);
                    $table->string('road', 100);
                    $table->string('description', 400);
                    $table->string('corrective_action', 400);
                    $table->boolean('corrective_action_applied');
                    $table->string('corrective_action_implementation', 400);
                    $table->string('comment', 400);
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    $table->integer('hazard_activity_id')->nullable()->unsigned()->default(NULL);
                    $table->timestamps();

                    //set up foreign keys
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                    $table->foreign('hazard_activity_id')->references('hazard_activity_id')->on('hazard_activities');
                    
                });
                
                Schema::create('hazard_has_hazard_category',function($table){
                    $table->integer('hazard_id')->unsigned();
                    $table->integer('hazard_category_id')->unsigned();
                    $table->unique(array('hazard_id','hazard_category_id') );
                    $table->foreign('hazard_id')->references('hazard_id')->on('hazards')->onDelete('cascade');
                    $table->foreign('hazard_category_id')->references('hazard_category_id')->on('hazard_categories');
                    
                });
                
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('hazard_has_hazard_category');
                Schema::drop('hazards');
                Schema::drop('hazard_activities');
                Schema::drop('hazard_categories');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class AddTailgate extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		    
                Schema::create('tailgates',function($table){
                    $table->increments('tailgate_id');
                    $table->string('title', 100);
                    $table->string('job_description', 400);
                    $table->string('stars_site', 100);
                    $table->string('phone_number', 100);
                    $table->string('job_number', 100);
                    $table->smallInteger('assessment_type');
                    $table->string('comment', 400);
                    
                    $table->boolean('fit_for_duty');
                    $table->string('fit_for_duty_description',400);
                    $table->boolean('proper_training');
                    $table->string('proper_training_description',400);
                    $table->boolean('job_scope_and_procedures');
                    $table->string('job_scope_and_procedures_description',400);
                    $table->boolean('hazards_identified');
                    $table->string('hazards_identified_description',400);
                    $table->boolean('controls_implemented');
                    $table->string('controls_implemented_description',400);
                    
                    
                    $table->string('created_at',50);
                    $table->integer('ts');

                    //set up foreign keys
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                });
                
                
                Schema::create('tailgate_tasks',function($table){
                    $table->increments('tailgate_task_id');
                    $table->string('title', 100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_task_hazards',function($table){
                    $table->increments('tailgate_task_hazard_id');
                    $table->string('description', 100);
                    $table->integer('risk_level');
                    $table->string('eliminate_hazard', 100);
                    $table->integer('risk_assessment');
                    $table->integer('tailgate_task_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_task_id')->references('tailgate_task_id')->on('tailgate_tasks')->onDelete('cascade');
                });
                
                
                Schema::create('tailgate_signoff_workers',function($table){
                    $table->increments('signoff_worker_id');
                    $table->string('first_name', 100);
                    $table->string('last_name', 100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_signoff_visitors',function($table){
                    $table->increments('signoff_visitor_id');
                    $table->string('first_name', 100);
                    $table->string('last_name', 100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_supervisors',function($table){
                    $table->increments('tailgate_supervisor_id');
                    $table->string('name',100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_permits',function($table){
                    $table->increments('tailgate_permit_id');
                    $table->string('permit_number',100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_locations',function($table){
                    $table->increments('tailgate_location_id');
                    $table->string('location',100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_lsds',function($table){
                    $table->increments('tailgate_lsd_id');
                    $table->string('lsd',100);
                    $table->integer('tailgate_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                });
                
                Schema::create('tailgate_has_hazard_items',function($table){
                    $table->integer('tailgate_id')->unsigned();
                    $table->integer('flha_hazard_item_id')->unsigned();
                    $table->unique(array('tailgate_id','flha_hazard_item_id'));
                    $table->foreign('tailgate_id')->references('tailgate_id')->on('tailgates')->onDelete('cascade');
                    $table->foreign('flha_hazard_item_id')->references('flha_hazard_item_id')->on('flha_hazard_items');
                    
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tailgate_has_hazard_items');
		Schema::drop('tailgate_lsds');
		Schema::drop('tailgate_locations');
		Schema::drop('tailgate_permits');
		Schema::drop('tailgate_supervisors');
		Schema::drop('tailgate_signoff_visitors');
		Schema::drop('tailgate_signoff_workers');
		Schema::drop('tailgate_task_hazards');
		Schema::drop('tailgate_tasks');
		Schema::drop('tailgates');
	}

}
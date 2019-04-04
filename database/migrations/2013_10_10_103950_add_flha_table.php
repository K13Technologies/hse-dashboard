<?php

use Illuminate\Database\Migrations\Migration;

class AddFlhaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('hazard_checklist_categories',function($table){
                    $table->increments('checklist_category_id');
                    $table->string('category_name', 100);
                });
                
                Schema::create('hazard_checklist_items',function($table){
                    $table->increments('checklist_item_id');
                    $table->string('name', 100);
                    $table->integer('checklist_category_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('checklist_category_id')->references('checklist_category_id')->on('hazard_checklist_categories');
                });
                
                
                Schema::create('flhas',function($table){
                    $table->increments('flha_id');
                    $table->string('title', 100);
                    $table->string('location', 100);
                    $table->string('site', 100);
                    $table->string('specific_location', 100);
                    $table->string('client', 100);
                    $table->string('muster_point', 100);
                    $table->string('site_emergency_number', 15);
                    $table->string('safety_rep_number', 15);
                    $table->string('radio_channel', 10);
                    $table->string('supervisor_name', 100);
                    $table->string('job_description', 400);
                    $table->boolean('gloves_removed');
                    $table->string('gloves_removed_description', 400);
                    $table->boolean('working_alone');
                    $table->string('working_alone_description', 400);
                    $table->boolean('warning_ribbon');
                    $table->string('warning_ribbon_description', 400);
                    
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    $table->timestamps();

                    //set up foreign keys
                    $table->foreign('worker_id')->references('worker_id')->on('workers');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('flhas');
                Schema::drop('hazard_checklist_items');
                Schema::drop('hazard_checklist_categories');
	}

}
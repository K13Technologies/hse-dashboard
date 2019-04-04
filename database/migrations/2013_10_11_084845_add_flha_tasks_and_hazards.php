<?php

use Illuminate\Database\Migrations\Migration;

class AddFlhaTasksAndHazards extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::drop('hazard_checklist_items');
                Schema::drop('hazard_checklist_categories');
                
                Schema::table('flhas',function($table){
                      $table->softDeletes();
                });
              
                Schema::create('flha_tasks',function($table){
                    $table->increments('flha_task_id');
                    $table->string('title', 100);
                    $table->timestamps();
                    $table->integer('flha_id')->nullable()->unsigned()->default(NULL);
                    $table->softDeletes();
                    
                    $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
                });
                
                Schema::create('flha_task_hazards',function($table){
                    $table->increments('flha_task_hazard_id');
                    $table->string('description', 100);
                    $table->integer('risk_level');
                    $table->string('eliminate_hazard', 100);
                    $table->integer('risk_assessment');
                    $table->timestamps();
                    $table->softDeletes();
                    
                    $table->integer('flha_task_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('flha_task_id')->references('flha_task_id')->on('flha_tasks')->onDelete('cascade');
                });
                
//                $hks = new HazardChecklistSeeder();
//                $hks->run();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{       
                Schema::drop('flha_task_hazards');
                Schema::drop('flha_tasks');
                
                Schema::table('flhas',function($table){
                      $table->dropColumn('deleted_at');
                });
                
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
                
                
                
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class AddFlhaHazardChecklistTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('flha_hazard_categories',function($table){
                    $table->increments('flha_hazard_category_id');
                    $table->string('category_name', 100);
                });
            
                Schema::create('flha_hazard_items',function($table){
                    $table->increments('flha_hazard_item_id');
                    $table->string('item_name', 200);
                    $table->integer('flha_hazard_category_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('flha_hazard_category_id')->references('flha_hazard_category_id')->on('flha_hazard_categories');
                });
                
                Schema::create('flha_has_hazard_items',function($table){
                    $table->integer('flha_id')->unsigned();
                    $table->integer('flha_hazard_item_id')->unsigned();
                    $table->unique(array('flha_id','flha_hazard_item_id'));
                    $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
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
                Schema::drop('flha_has_hazard_items');
                Schema::drop('flha_hazard_items');
                Schema::drop('flha_hazard_categories');
	}

}
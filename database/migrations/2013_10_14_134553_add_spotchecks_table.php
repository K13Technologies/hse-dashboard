<?php

use Illuminate\Database\Migrations\Migration;

class AddSpotchecksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('flha_spotchecks',function($table){
                    $table->increments('spotcheck_id');
                    $table->string('first_name', 100);
                    $table->string('last_name', 100);
                    $table->string('position', 100);
                    $table->boolean('flha_validity');
                    $table->string('flha_validity_description',400);
                    $table->boolean('critical_hazard');
                    $table->string('critical_hazard_description',400);
                    $table->boolean('crew_list_complete');
                    $table->string('crew_description',400);
                    $table->timestamps();
                    $table->softDeletes();
                    $table->integer('flha_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('flha_spotchecks');
	}

}
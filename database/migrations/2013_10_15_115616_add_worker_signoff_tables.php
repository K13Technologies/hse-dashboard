<?php

use Illuminate\Database\Migrations\Migration;

class AddWorkerSignoffTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('flha_signoff_workers',function($table){
                    $table->increments('signoff_worker_id');
                    $table->string('first_name', 100);
                    $table->string('last_name', 100);
                    $table->timestamps();
                    $table->softDeletes();
                    $table->integer('flha_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
                });
                
                Schema::create('flha_signoff_break',function($table){
                    $table->increments('signoff_break_id');
                    $table->integer('type');
                    $table->timestamps();
                    $table->softDeletes();
                    $table->integer('signoff_worker_id')->nullable()->unsigned()->default(NULL);
                    
                    $table->foreign('signoff_worker_id')->references('signoff_worker_id')->on('flha_signoff_workers')->onDelete('cascade');
                });
                
                Schema::create('flha_signoff_visitors',function($table){
                    $table->increments('signoff_visitor_id');
                    $table->string('first_name', 100);
                    $table->string('last_name', 100);
                    $table->string('company', 100);
                    $table->string('position', 100);
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
                Schema::drop('flha_signoff_visitors');
                Schema::drop('flha_signoff_break');
                Schema::drop('flha_signoff_workers');
	}

}
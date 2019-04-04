<?php

use Illuminate\Database\Migrations\Migration;

class AddPartsForIncident extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('incident_schemas',function($table){
                    $table->increments('incident_schema_id');
                    $table->string('key',100);
                    $table->smallInteger('type');
                });
                
                Schema::create('incident_schema_parts',function($table){
                    $table->increments('incident_schema_part_id');
                    $table->string('key',100);
                    $table->string('description',100);
                    $table->integer('x1');
                    $table->integer('y1');
                    $table->integer('x2');
                    $table->integer('y2');
                    $table->integer('incident_schema_id')->nullable()->unsigned()->default(NULL);

                    $table->foreign('incident_schema_id')->references('incident_schema_id')->on('incident_schemas')->onDelete('cascade');
                });
                
                Schema::create('incident_part_statements',function($table){
                    $table->increments('part_statement_id');
                    $table->integer('incident_id')->nullable()->unsigned()->default(NULL);
                    $table->integer('incident_schema_part_id')->nullable()->unsigned()->default(NULL);
                    $table->string('comment',1000);
                });
                
                
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('incident_part_statements');
                Schema::drop('incident_schema_parts');
                Schema::drop('incident_schemas');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class AddInspectionActionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Schema::create('inspection_actions',function($table){
                    $table->increments('inspection_action_id');
                    $table->string('action_type', 100);
                    $table->string('action', 500);
                    $table->timestamps();
                    $table->date('completed_on')->nullable()->default(NULL);
                    
                    $table->integer('inspection_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                    $table->foreign('inspection_id')->references('inspection_id')->on('inspections')->onDelete('cascade');
                });
                
                Schema::table('inspections',function($table){
                    $table->boolean('action_required')->after('interior_tire_wrench');
                });
                
                foreach(Inspection::all() as $inspection){
                    $inspection->buildActions();
                }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('inspection_actions');
                
                Schema::table('inspections',function($table){
                    $table->dropColumn('action_required');
                });
	}

}
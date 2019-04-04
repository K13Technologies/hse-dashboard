<?php

use Illuminate\Database\Migrations\Migration;

class AddVehicleTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('vehicles',function($table){
                $table->increments('vehicle_id');
                $table->string('license_plate',20)->unique();
                $table->string('vehicle_number',50)->unique();
                $table->string('color',20);
                $table->float('mileage',10,2);
                $table->integer('group_id')->nullable()->unsigned()->default(NULL);
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                $table->timestamps();
                
                //set up foreign keys
                $table->foreign('group_id')->references('group_id')->on('groups');
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
	    Schema::drop('vehicles');
	}

}
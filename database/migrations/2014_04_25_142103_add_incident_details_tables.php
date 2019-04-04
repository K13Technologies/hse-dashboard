<?php

use Illuminate\Database\Migrations\Migration;

class AddIncidentDetailsTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('incident_persons',function($table){
               $table->increments('incident_person_id');
               $table->string('first_name',100);
               $table->string('last_name',100);
               $table->string('phone_number',50);
               $table->string('company',200);
               $table->string('time_on_shift',50)->default(NULL);
               $table->string('time_of_incident',50)->default(NULL);
               $table->string('statement',1000);
               $table->integer('ts_on_shift')->nullable()->unsigned()->default(NULL);
               $table->integer('ts_of_incident')->nullable()->unsigned()->default(NULL);
               $table->smallInteger('employment_status');
               $table->smallInteger('type');
               $table->integer('incident_id')->nullable()->unsigned()->default(NULL);

               $table->foreign('incident_id')->references('incident_id')->on('incidents')->onDelete('cascade');
            });
            
            Schema::create('incident_mvds',function($table){
                $table->increments('incident_mvd_id');
                $table->string('driver_license_number',100);
                $table->string('insurance_company',500);
                $table->string('insurance_policy_number',500);
                $table->string('policy_expiry_date',50);
                $table->integer('vehicle_year');
                $table->string('make',100);
                $table->string('model',300);
                $table->string('vin',100);
                $table->string('color',50);
                $table->string('license_plate',100);
                $table->string('time_of_incident',50);
                $table->integer('ts_of_incident')->nullable()->unsigned()->default(NULL);
                $table->boolean('tdg');
                $table->string('tdg_material',300);
                $table->boolean('wearing_seatbelts');
                $table->string('wearing_seatbelts_description',300);
                $table->boolean('airbags_deployed');
                $table->boolean('damage_exceeds_amount');
                $table->string('police_file_number',100);
                $table->boolean('attending_police_officer');
                $table->string('police_service',200);
                $table->string('police_name',100);
                $table->string('police_badge_number',100);
                $table->string('police_business_phone_number',50);
                $table->boolean('vehicle_towed');
                $table->string('tow_company',500);
                $table->string('tow_driver_name',100);
                $table->string('tow_business_phone_number',50);
                $table->string('tow_address',500);
                $table->boolean('other_passengers');
                $table->string('other_passengers_details',500);
                $table->smallInteger('vehicles_involved')->unsigned();
                $table->boolean('my_vehicle_damaged');
                $table->string('comment',1000);
                
                $table->smallInteger('vehicleType')->unsigned();
                
                $table->integer('incident_id')->nullable()->unsigned()->default(NULL);
                
                
            });
            
            Schema::create('incident_treatments',function($table){
                $table->increments('incident_treatment_id');
                $table->boolean('first_aid');
                $table->boolean('medical_aid');
                $table->string('responder_name',100);
                $table->string('responder_company',500);
                $table->string('responder_phone_number',50);
                $table->string('comment',1000);
                $table->boolean('injuries');
                
                $table->integer('incident_id')->nullable()->unsigned()->default(NULL);
            });
            
            Schema::create('incident_release_spills',function($table){
                $table->increments('incident_release_spill_id');
                $table->string('commodity',200);
                $table->boolean('potential_exposure');
                $table->string('release_source',500);
                $table->string('release_to',500);
                $table->integer('quantity_released');
                $table->string('quantity_released_unit',20);
                $table->integer('quantity_recovered');
                $table->string('quantity_recovered_unit',20);
                $table->string('comment',1000);
                
                $table->integer('incident_id')->nullable()->unsigned()->default(NULL);
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
             Schema::drop('incident_treatments');
             Schema::drop('incident_mvds');
             Schema::drop('incident_persons');
	}

}
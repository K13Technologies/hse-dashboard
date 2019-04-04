<?php

use Illuminate\Database\Migrations\Migration;

class AddInspectionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('inspections',function($table){
                $table->increments('inspection_id');
                $table->string('location',100);
                $table->string('mileage',100);
                $table->string('comment',100);
                $table->string('visual_dents',100);
                $table->string('visual_tires',100);
                $table->string('visual_rims',100);
                $table->string('visual_mud_flaps',100);
                $table->string('visual_lights_and_reflections',100);
                $table->string('visual_broken_headlights',100);
                $table->string('visual_broken_taillights',100);
                $table->string('visual_license_plate',100);
                $table->string('visual_mirrors',100);
                $table->string('visual_spare_tire',100);
                $table->string('visual_windshield',100);
                $table->string('engine_coolant',100);
                $table->string('engine_battery',100);
                $table->string('engine_brakes',100);
                $table->string('engine_heater',100);
                $table->string('engine_defroster',100);
                $table->string('engine_horn',100);
                $table->string('engine_seatbelts',100);
                $table->string('engine_radiator',100);
                $table->string('engine_engine',100);
                $table->string('engine_muffler',100);
                $table->string('engine_wippers',100);
                $table->string('interior_first_aid',100);
                $table->string('interior_fire_extinguisher',100);
                $table->string('interior_flare_kit',100);
                $table->string('iterior_warning_triangles',100);
                $table->string('interior_tow_rope',100);
                $table->string('interior_whip_flag',100);
                $table->string('interior_radio',100);
                $table->string('interior_insurance',100);
                $table->string('interior_registration',100);
                $table->string('interior_vehicle_jack',100);
                $table->string('interior_tire_wrench',100);
                
                $table->integer('vehicle_id')->nullable()->unsigned()->default(NULL);
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                $table->timestamps();
                
                //set up foreign keys
                $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles');
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
		Schema::drop('inspections');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class DbTextChanges extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
                Trade::all()->each(function($trade){
                    $trade->formatString();
                    $trade->save();
                });
                HazardCategory::all()->each(function($hazardCategory){
                    $hazardCategory->formatString();
                    $hazardCategory->save();
                });
                PositiveObservationCategory::all()->each(function($poCategory){
                    $poCategory->formatString();
                    $poCategory->save();
                });
                HazardChecklistCategory::all()->each(function($hcCategory){
                    $hcCategory->formatString();
                    $hcCategory->save();
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
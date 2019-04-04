<?php

use Illuminate\Database\Migrations\Migration;

class ChangeJourneysTz extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            
            $td = head(DB::select( DB::raw("SELECT TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP)) as secs")));
            $timediff = (int)$td->secs;
            
            $mst = 21600;
            $mdt = 25200;
            
            $origOffsetMDT = $mdt+$timediff;
            $origOffsetMST = $mst+$timediff;
            
            Schema::table('journeys',function($table){
                $table->integer('ts_created');
                $table->integer('ts_started');
                $table->integer('ts_finished');
            });
            DB::statement('update journeys set ts_created= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update journeys set ts_created= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement('update journeys set ts_started= UNIX_TIMESTAMP(`started_at`)+'.$origOffsetMDT.' where started_at >="2014-03-09 00:00:00"');
            DB::statement('update journeys set ts_started= UNIX_TIMESTAMP(`started_at`)+'.$origOffsetMST.' where started_at < "2014-03-09 00:00:00"');
            DB::statement('update journeys set ts_finished= UNIX_TIMESTAMP(`finished_at`)+'.$origOffsetMDT.' where finished_at >="2014-03-09 00:00:00"');
            DB::statement('update journeys set ts_finished= UNIX_TIMESTAMP(`finished_at`)+'.$origOffsetMST.' where finished_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `journeys` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE  `journeys` CHANGE  `started_at`  `started_at` VARCHAR(50) NOT NULL");
            DB::statement("ALTER TABLE  `journeys` CHANGE  `finished_at`  `finished_at` VARCHAR(50) NOT NULL");
            DB::statement('update journeys set created_at= CONCAT(created_at," GMT-7")');
            DB::statement('update journeys set started_at= CONCAT(started_at," GMT-7")');
            DB::statement('update journeys set finished_at= CONCAT(finished_at," GMT-7")');
            
            Schema::table('journey_checkins',function($table){
                $table->integer('ts');
            });
            DB::statement('update journey_checkins set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update journey_checkins set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `journey_checkins` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update journey_checkins set created_at= CONCAT(created_at," GMT-7")');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            DB::statement("ALTER TABLE  `journeys` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            DB::statement("ALTER TABLE  `journeys` CHANGE  `started_at`  `started_at` Datetime NOT NULL");
            DB::statement("ALTER TABLE  `journeys` CHANGE  `finished_at`  `finished_at` Datetime NOT NULL");
            Schema::table('journeys',function($table){
                $table->dropColumn('ts_created');
                $table->dropColumn('ts_started');
                $table->dropColumn('ts_finished');
            });
            
            DB::statement("ALTER TABLE  `journey_checkins` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('journey_checkins',function($table){
                $table->dropColumn('ts');
            });
	}

}
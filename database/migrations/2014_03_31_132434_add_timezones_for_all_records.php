<?php

use Illuminate\Database\Migrations\Migration;

class AddTimezonesForAllRecords extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{       
            Schema::table('admins',function($table){
                $table->integer('tz_offset');
            });
//            
            
            $td = head(DB::select( DB::raw("SELECT TIME_TO_SEC(TIMEDIFF(NOW(), UTC_TIMESTAMP)) as secs")));
            $timediff = (int)$td->secs;
            
            $mst = 21600;
            $mdt = 25200;
            
            $origOffsetMDT = $mdt+$timediff;
            $origOffsetMST = $mst+$timediff;

            //hazards
            Schema::table('hazards',function($table){
                $table->integer('ts');
            });
            DB::statement('update hazards set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update hazards set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `hazards` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update hazards set created_at= CONCAT(created_at," GMT-7")');
            //near misses
            Schema::table('near_misses',function($table){
                $table->integer('ts');
            });
            DB::statement('update near_misses set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update near_misses set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `near_misses` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update near_misses set created_at= CONCAT(created_at, " GMT-7")');
           
            //positive observations
            Schema::table('positive_observations',function($table){
                $table->integer('ts');
            });
            DB::statement('update positive_observations set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update positive_observations set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `positive_observations` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update positive_observations set created_at= CONCAT(created_at, " GMT-7")');
           
            //flha
            Schema::table('flhas',function($table){
                $table->integer('ts');
            });
            DB::statement('update flhas set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update flhas set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `flhas` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update flhas set created_at= CONCAT(created_at, " GMT-7")');
            
            //breaks 
            Schema::table('flha_signoff_breaks',function($table){
                $table->integer('ts');
            });
            DB::statement('update flha_signoff_breaks set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update flha_signoff_breaks set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `flha_signoff_breaks` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update flha_signoff_breaks set created_at= CONCAT(created_at, " GMT-7")');
         
            //spotchecks observations
            Schema::table('flha_spotchecks',function($table){
                $table->integer('ts');
            });
            DB::statement('update flha_spotchecks set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update flha_spotchecks set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `flha_spotchecks` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update flha_spotchecks set created_at= CONCAT(created_at, " GMT-7")');
            
            Schema::table('inspections',function($table){
                $table->integer('ts');
            });
            DB::statement('update inspections set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update inspections set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `inspections` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
            DB::statement('update inspections set created_at= CONCAT(created_at, " GMT-7")');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::table('admins',function($table){
                $table->dropColumn('tz_offset');
            });
            
            DB::statement("ALTER TABLE  `hazards` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('hazards',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `near_misses` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('near_misses',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `positive_observations` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('positive_observations',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `flhas` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('flhas',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `flha_signoff_breaks` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('flha_signoff_breaks',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `flha_spotchecks` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('flha_spotchecks',function($table){
                $table->dropColumn('ts');
            });
            DB::statement("ALTER TABLE  `inspections` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('inspections',function($table){
                $table->dropColumn('ts');
            });
	}

}
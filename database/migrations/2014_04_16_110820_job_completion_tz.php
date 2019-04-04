<?php

use Illuminate\Database\Migrations\Migration;

class JobCompletionTz extends Migration {

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
            
            Schema::table('job_completions',function($table){
                $table->integer('ts');
                $table->dropColumn('updated_at');
            });
            DB::statement('update job_completions set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMDT.' where created_at >="2014-03-09 00:00:00"');
            DB::statement('update job_completions set ts= UNIX_TIMESTAMP(`created_at`)+'.$origOffsetMST.' where created_at < "2014-03-09 00:00:00"');
            DB::statement("ALTER TABLE  `job_completions` CHANGE  `created_at`  `created_at` VARCHAR(50) NOT NULL");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            DB::statement("ALTER TABLE  `job_completions` CHANGE  `created_at`  `created_at` Datetime NOT NULL");
            Schema::table('job_completions',function($table){
                $table->dropColumn('ts');
                $table->datetime('updated_at');
            });
	}

}
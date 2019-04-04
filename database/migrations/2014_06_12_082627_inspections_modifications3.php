<?php

use Illuminate\Database\Migrations\Migration;

class InspectionsModifications3 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{   
            
            Schema::table('photos',function($table){
                $table->string('used_for',50);
            });
            Schema::table('inspection_actions',function($table){
                $table->boolean('is_worker_added')->after('action');
            });
            
            foreach (Inspection::getProperties() as $p){
                if (preg_match("/^interior.*|engine.*|visual.*/", $p)) {
                    DB::statement("ALTER TABLE  `inspections` CHANGE  `{$p}`  `{$p}` VARCHAR(200) DEFAULT NULL");
                }
            }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{   
            Schema::table('photos',function($table){
                $table->dropColumn('used_for');
            });
            Schema::table('inspection_actions',function($table){
                $table->dropColumn('is_worker_added');
            });
            
            foreach (Inspection::getProperties() as $p){
                if (preg_match("/^interior.*|engine.*|visual.*/", $p)) {
                    DB::statement("ALTER TABLE  `inspections` CHANGE  `{$p}`  `{$p}` VARCHAR(100) NOT NULL");
                }
            }
	}

}
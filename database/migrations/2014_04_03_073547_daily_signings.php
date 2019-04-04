<?php

use Illuminate\Database\Migrations\Migration;

class DailySignings extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('daily_signins',function($table){
                $table->bigIncrements('daily_signin_id');
                $table->integer('ts');
                $table->string('created_at',50);
                $table->boolean('type');
                $table->string('name',200);
                $table->integer('group_id')->nullable()->unsigned()->default(NULL);
                $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::drop('daily_signins');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class AddPhoneNoAndRadioForCompany extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('company_helplines',function($table){
                $table->increments('helpline_id');
                $table->string('title', 200);
                $table->string('value', 50);
                $table->boolean('type');
                $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                $table->foreign('company_id')->references('company_id')->on('companies')->onDelete('cascade');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
            Schema::drop('company_helplines');
	}

}
<?php

use Illuminate\Database\Migrations\Migration;

class MultipleEmergencyContacts extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('emergency_contacts',function($table){
                $table->increments('emergency_contact_id');
                $table->string('name',100);
                $table->string('contact',100);
                $table->string('relationship',100);
                $table->integer('worker_id')->unsigned()->nullable();
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	    Schema::drop('emergency_contacts');
	}

}
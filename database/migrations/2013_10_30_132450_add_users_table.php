<?php

use Illuminate\Database\Migrations\Migration;

class AddUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('admins',function($table){
                    $table->increments('admin_id');
                    $table->string('email', 150)->unique();
                    $table->string('password', 128);
                    $table->string('reset_token', 128);
                    $table->string('first_name', 50);
                    $table->string('last_name', 50);
                    $table->boolean('is_active');
                    $table->timestamps();
                    $table->softDeletes();
                    $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                    $table->integer('role_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
                    $table->foreign('company_id')->references('company_id')->on('companies');
                    $table->foreign('role_id')->references('user_role_id')->on('user_roles');
                });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('admins');
	}

}
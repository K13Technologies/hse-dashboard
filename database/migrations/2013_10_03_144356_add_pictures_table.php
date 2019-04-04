<?php

use Illuminate\Database\Migrations\Migration;

class AddPicturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('photos',function($table){
                    $table->increments('photo_id');
                    $table->string('path', 100);
                    $table->string('name', 100)->unique();
                    $table->integer('imageable_id')->unsigned();
                    $table->string('imageable_type',50);
                    $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                    //set up foreign keys
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
		Schema::drop('photos');
	}

}
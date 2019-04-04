<?php

use Illuminate\Database\Migrations\Migration;

class FlhaUpdates extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('flha_sites', function($table) {
            $table->increments('flha_site_id');
            $table->string('site', 100);
            $table->integer('flha_id')->nullable()->unsigned()->default(NULL);

            $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
        });

        Schema::create('flha_permits', function($table) {
            $table->increments('flha_permit_id');
            $table->string('permit_number', 100);
            $table->integer('flha_id')->nullable()->unsigned()->default(NULL);

            $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
        });

        Schema::create('flha_locations', function($table) {
            $table->increments('flha_location_id');
            $table->string('location', 100);
            $table->integer('flha_id')->nullable()->unsigned()->default(NULL);

            $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
        });

        Schema::create('flha_lsds', function($table) {
            $table->increments('flha_lsd_id');
            $table->string('lsd', 100);
            $table->integer('flha_id')->nullable()->unsigned()->default(NULL);

            $table->foreign('flha_id')->references('flha_id')->on('flhas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('flha_lsds');
        Schema::drop('flha_locations');
        Schema::drop('flha_permits');
        Schema::drop('flha_sites');
    }

}

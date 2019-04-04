<?php

use Illuminate\Database\Migrations\Migration;

class AddMultipleMvdMt extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('incident_mvds', function($table) {
            $table->integer('incident_type_id')->nullable()->unsigned()->default(NULL);
        });
        Schema::table('incident_treatments', function($table) {
            $table->integer('incident_type_id')->nullable()->unsigned()->default(NULL);
        });
        Schema::table('incident_part_statements', function($table) {
            $table->integer('statementable_id')->unsigned();
            $table->string('statementable_type', 50);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('incident_mvds', function($table) {
            $table->dropColumn('incident_type_id');
        });
        Schema::table('incident_treatments', function($table) {
            $table->dropColumn('incident_type_id');
        });
        Schema::table('incident_part_statements', function($table) {
            $table->dropColumn('statementable_id');
            $table->dropColumn('statementable_type');
        });
    }

}

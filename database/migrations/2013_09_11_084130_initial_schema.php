<?php

use Illuminate\Database\Migrations\Migration;

class InitialSchema extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{   
            
            //create trades table
            Schema::create('trades',function($table){
                $table->increments('trade_id')->unsigned();
                $table->string('trade_name', 200);
            }); 
            
            //create user roles table
            Schema::create('user_roles',function($table){
                $table->increments('user_role_id')->unsigned();
                $table->string('role_name', 200);
            }); 
            
            //create companies table
            Schema::create('companies',function($table){
                $table->increments('company_id')->unsigned();
                $table->string('company_name', 100)->unique();
            }); 
            
            
            //create divisions table
            Schema::create('divisions',function($table){
                $table->increments('division_id')->unsigned();
                $table->string('division_name', 100);
                $table->integer('company_id')->unsigned();
                //set up foreign key
                $table->foreign('company_id')->references('company_id')->on('companies');
                $table->unique('division_name','company_id');
            }); 
       
            //create business_units table
            Schema::create('business_units',function($table){
                $table->increments('business_unit_id')->unsigned();
                $table->string('business_unit_name', 100);
                $table->integer('division_id')->unsigned();
                //set up foreign key
                $table->foreign('division_id')->references('division_id')->on('divisions');
                $table->unique('business_unit_name','division_id');
            }); 
            
            //create groups table
            Schema::create('groups',function($table){
                $table->increments('group_id')->unsigned();
                $table->string('group_name', 100);
                $table->integer('business_unit_id')->unsigned();
                //set up foreign key
                $table->foreign('business_unit_id')->references('business_unit_id')->on('business_units');
                $table->unique('group_name','business_unit_id');
            }); 
            
            //create projects table
            Schema::create('projects',function($table){
                $table->increments('project_id')->unsigned();
                $table->string('project_name', 100);
                $table->integer('group_id')->unsigned();
                //set up foreign key
                $table->foreign('group_id')->references('group_id')->on('groups');
            }); 
            
            //create projects table
            Schema::create('clients',function($table){
                $table->increments('client_id')->unsigned();
                $table->string('client_name', 100);
                $table->string('client_site', 100);
                $table->string('specific_area', 200);
                $table->integer('project_id')->nullable()->unsigned()->default(NULL);
                $table->integer('group_id')->nullable()->unsigned()->default(NULL);
                $table->integer('business_unit_id')->nullable()->unsigned()->default(NULL);
                $table->integer('division_id')->nullable()->unsigned()->default(NULL);
                $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                
                //set up foreign keys
                $table->foreign('group_id')->references('group_id')->on('groups');
                $table->foreign('business_unit_id')->references('business_unit_id')->on('business_units');
                $table->foreign('division_id')->references('division_id')->on('divisions');
                $table->foreign('company_id')->references('company_id')->on('companies');
                $table->foreign('project_id')->references('project_id')->on('projects');
            }); 
            
            //create worker table
            Schema::create('workers',function($table){
                $table->increments('worker_id')->unsigned();
                $table->string('auth_token', 10)->unique();
                $table->string('api_key', 255)->unique();
                $table->boolean('profile_completed')->default(FALSE);
                $table->boolean('approved')->default(FALSE);
                $table->date('approved_date')->nullable()->default(NULL);
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->date('birthday')->nullable()->default(NULL);
                $table->string('home_phone', 15);
                $table->string('cell_phone', 15);
                $table->string('work_phone', 15);
                $table->string('work_cell_phone', 15);
                $table->string('street', 200);
                $table->string('suite', 200);
                $table->string('city', 100);
                $table->string('state', 100);
                $table->string('country', 100);
                $table->string('zip', 8);
                $table->string('site', 50);
                $table->integer('primary_trade_id')->nullable()->unsigned()->default(NULL);
                $table->decimal('primary_trade_seniority',5,2);
                $table->integer('secondary_trade_id')->nullable()->unsigned()->default(NULL);
                $table->decimal('secondary_trade_seniority',5,2)->default(NULL);
                $table->integer('other_trade_id')->nullable()->unsigned()->default(NULL);
                $table->decimal('other_trade_seniority',5,2)->default(NULL);
                $table->integer('project_id')->nullable()->unsigned()->default(NULL);
                $table->integer('group_id')->nullable()->unsigned()->default(NULL);
                $table->integer('business_unit_id')->nullable()->unsigned()->default(NULL);
                $table->integer('division_id')->nullable()->unsigned()->default(NULL);
                $table->integer('company_id')->nullable()->unsigned()->default(NULL);
                
                //set up foreign keys
                $table->foreign('group_id')->references('group_id')->on('groups');
                $table->foreign('business_unit_id')->references('business_unit_id')->on('business_units');
                $table->foreign('division_id')->references('division_id')->on('divisions');
                $table->foreign('company_id')->references('company_id')->on('companies');
                $table->foreign('project_id')->references('project_id')->on('projects');
                $table->foreign('primary_trade_id')->references('trade_id')->on('trades');
                $table->foreign('secondary_trade_id')->references('trade_id')->on('trades');
                $table->foreign('other_trade_id')->references('trade_id')->on('trades');
            }); 
            
                 //create workers_has_clients table
                Schema::create('worker_has_clients',function($table){
                $table->increments('worker_has_clients_id')->unsigned();
                $table->integer('worker_id')->nullable()->unsigned()->default(NULL);
                $table->integer('client_id')->nullable()->unsigned()->default(NULL);
                $table->boolean('active');
                $table->date('date_start')->nullable()->default(NULL);
                $table->date('date_end')->nullable()->default(NULL);
                
                //set up foreign keys
                $table->foreign('worker_id')->references('worker_id')->on('workers');
                $table->foreign('client_id')->references('client_id')->on('clients');
            }); 
            
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
                Schema::drop('worker_has_clients');

                Schema::drop('workers');
                    
                Schema::drop('clients');
            
                Schema::drop('projects');
                Schema::drop('groups');
                Schema::drop('business_units');
                Schema::drop('divisions');
                Schema::drop('companies');
                
		Schema::drop('user_roles');
                
                Schema::drop('trades');
		
	}

}
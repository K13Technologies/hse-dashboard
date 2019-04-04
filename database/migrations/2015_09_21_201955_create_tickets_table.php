<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tickets', function(Blueprint $table)
		{
			$table->increments('ticket_id');
			$table->string('type_name', 3000);
			$table->string('issuer_organization_name', 3000);
			$table->string('description', 3000);

			$table->boolean('issued_internally');

			$table->integer('worker_id')->unsigned();
			$table->integer('created_by_admin_id')->unsigned();
			$table->integer('company_id')->unsigned();

			$table->timestamp('expiry_date')->default(NULL);
			$table->timestamps();
			
			$table->integer('ts_create_date');
			$table->integer('ts_expiry_date');

			$table->softDeletes();

			// Set up foreign keys
			$table->foreign('worker_id')->references('worker_id')->on('workers')->onDelete('cascade');
			$table->foreign('created_by_admin_id')->references('admin_id')->on('admins')->onDelete('cascade');
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
		/*Schema::table('tickets', function(Blueprint $table)
		{
			//
		});*/

		Schema::dropIfExists('tickets');
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MoveCashierToCompany extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('companies', function(Blueprint $table)
		{
                        $table->boolean('is_enterprise')->default(false);
                        $table->date('payment_due')->nullable()->default(NULL);
//                        
                        
			$table->tinyInteger('stripe_active')->default(0);
			$table->string('stripe_id')->nullable();
			$table->string('stripe_subscription')->nullable();
			$table->string('stripe_plan', 25)->nullable();
			$table->string('last_four', 4)->nullable();
			$table->timestamp('trial_ends_at')->nullable();
			$table->timestamp('subscription_ends_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('companies', function(Blueprint $table)
		{
			$table->dropColumn( 'is_enterprise', 'payment_due',
				'stripe_active', 'stripe_id', 'stripe_subscription', 'stripe_plan', 'last_four', 'trial_ends_at', 'subscription_ends_at'
			);
		});
	}

}

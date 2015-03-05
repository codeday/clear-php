<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAmountReceived extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        \Schema::table('batches_events_registrations', function($table)
        {
            $table->dropColumn('amount_received');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        \Schema::table('batches_events_registrations', function($table)
        {
            $table->decimal('amount_received', 5, 2);
        });

        foreach (\CodeDay\Clear\Models\Batch\Event\Registration::all() as $registration) {
            // Skip registrations for deleted events
            if ($registration->event === null) {
                continue;
            }

            // Calculate amount_received
            if ($registration->amount_paid > 0) {
                $stripe_fee = ($registration->amount_paid * 0.027) + 0.30;
                $registration->amount_received = $registration->amount_paid - $stripe_fee;
            }


            $registration->save();
        }
	}

}

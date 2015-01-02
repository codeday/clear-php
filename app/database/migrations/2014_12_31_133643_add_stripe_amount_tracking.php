<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \CodeDay\Clear\Models;

class AddStripeAmountTracking extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        \Schema::table('batches_events_registrations', function($table)
        {
            $table->decimal('amount_received', 5, 2);
            $table->decimal('amount_refunded', 5, 2)->default(0);
            $table->boolean('is_earlybird_pricing');
        });

        foreach (Models\Batch\Event\Registration::all() as $registration) {
            // Skip registrations for deleted events
            if ($registration->event === null) {
                continue;
            }

            // Calculate amount_received
            if ($registration->amount_paid > 0) {
                $stripe_fee = ($registration->amount_paid * 0.027) + 0.30;
                $registration->amount_received = $registration->amount_paid - $stripe_fee;
            }


            // Figure out if the user was using earlybird pricing
            $earlybird_time_eligible = $registration->created_at->lte($registration->event->early_bird_ends_at);
            $earlybird_regs_before = Models\Batch\Event\Registration
                ::where('batches_event_id', '=', $registration->batches_event_id)
                ->where('created_at', '<', $registration->created_at)
                ->count();
            $earlybird_count_eligible = $earlybird_regs_before < $registration->event->early_bird_max_registrations;
            $registration->is_earlybird_pricing = $earlybird_time_eligible && $earlybird_count_eligible;


            // Figure out how much of a refund, if any, was applied
            $reg_base_price = $registration->is_earlybird_pricing ? 10 : 20;
            $reg_discount = $registration->promotion ? $registration->promotion->percent_discount : 0;
            $reg_expected_price = $reg_base_price * (1-($reg_discount/100));
            $registration->amount_refunded = $reg_expected_price - $registration->amount_paid;


            $registration->save();
        }
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
            $table->dropColumn('amount_received');
            $table->dropColumn('amount_refunded');
            $table->dropColumn('is_earlybird_pricing');
        });
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEvangelistFlightTracking extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_flights', function(Blueprint $table) {
			$table->increments('id');

			$table->string('batches_event_id');

			$table->string('airline');
			$table->string('flight_number');
			$table->string('confirmation_code');
			$table->string('from_airport');
			$table->datetime('departs_at');
			$table->string('to_airport');
			$table->datetime('arrives_at');

			$table->enum('direction', ['to', 'from']);
			$table->string('traveler_username');

			$table->datetime('checkin_reminder_sent_at')->null();

			$table->timestamps();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_events_flights');
    }

}

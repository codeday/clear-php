<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVenueEmails extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches', function($table)
		{
			$table->datetime('venue_reminder_email_sent_at')->nullable();
			$table->datetime('venue_postevent_email_sent_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\Schema::table('batches', function($table)
		{
			$table->dropColumn('venue_reminder_email_sent_at');
			$table->dropColumn('venue_postevent_email_sent_at');
		});
	}

}

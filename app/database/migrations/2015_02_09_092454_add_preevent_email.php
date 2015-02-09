<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreeventEmail extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches', function($table)
		{
			$table->datetime('reminder_email_sent_at')->nullable();
			$table->datetime('preevent_email_sent_at')->nullable();
		});

		\Schema::table('batches_events', function($table)
		{
			$table->text('preevent_additional')->nullable();
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
			$table->dropColumn('reminder_email_sent_at');
			$table->dropColumn('preevent_email_sent_at');
		});
		\Schema::table('batches_events', function($table)
		{
			$table->dropColumn('preevent_additional');
		});
	}

}

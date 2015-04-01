<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTicketType extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events_registrations', function($table)
		{
			$table->enum('type', ['student', 'teacher', 'volunteer', 'sponsor'])->default('student');
		});

		\Schema::table('batches_events_promotions', function($table)
		{
			$table->enum('type', ['student', 'teacher', 'volunteer', 'sponsor'])->default('student');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\Schema::table('batches_events_promotions', function($table)
		{
			$table->dropColumn('type');
		});

		\Schema::table('batches_events_registrations', function($table)
		{
			$table->dropColumn('type');
		});
	}

}

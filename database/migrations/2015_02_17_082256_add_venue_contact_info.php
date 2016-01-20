<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVenueContactInfo extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events', function($table)
		{
			$table->string('venue_contact_first_name')->after('venue_country')->nullable();
			$table->string('venue_contact_last_name')->after('venue_contact_first_name')->nullable();
			$table->string('venue_contact_email')->after('venue_contact_last_name')->nullable();
			$table->string('venue_contact_phone')->after('venue_contact_email')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\Schema::table('batches_events', function($table)
		{
			$table->dropColumn('venue_contact_first_name');
			$table->dropColumn('venue_contact_last_name');
			$table->dropColumn('venue_contact_email');
			$table->dropColumn('venue_contact_phone');
		});
	}

}

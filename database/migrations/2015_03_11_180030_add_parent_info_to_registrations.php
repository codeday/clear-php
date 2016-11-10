<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentInfoToRegistrations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events_registrations', function($table)
		{
			$table->string('parent_name')->nullable();
			$table->string('parent_email')->nullable();
			$table->string('parent_phone')->nullable();
			$table->string('parent_secondary_phone')->nullable();
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
			$table->dropColumn('parent_name');
			$table->dropColumn('parent_email');
			$table->dropColumn('parent_phone');
			$table->dropColumn('parent_secondary_phone');
		});
	}

}

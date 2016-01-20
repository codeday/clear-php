<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSponsorRevTracking extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events_sponsors', function($table)
		{
			$table->integer('amount');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\Schema::table('batches_events_sponsors', function($table)
		{
			$table->dropColumn('amount');
		});
	}

}

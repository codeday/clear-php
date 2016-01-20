<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddS5InviteCodeToRegistrations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events_registrations', function($table)
		{
			$table->string('s5_invite_code')->nullable();
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
			$table->dropColumn('s5_invite_code');
		});
	}

}

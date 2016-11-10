<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPublicNotesToEvents extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('batches_events', function($table)
		{
			$table->text('public_notes')->nullable();
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
			$table->dropColumn('public_notes');
		});
	}

}

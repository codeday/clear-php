<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoggedInAt extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::table('users', function($table)
		{
			$table->datetime('logged_in_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		\Schema::table('users', function($table)
		{
			$table->dropColumn('logged_in_at');
		});
	}

}

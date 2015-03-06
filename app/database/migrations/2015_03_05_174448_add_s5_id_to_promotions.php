<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddS5IdToPromotions extends Migration {

	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		\Schema::table('batches_events_promotions', function($table)
		{
			$table->string('created_by_user')->nullable();
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
			$table->dropColumn('created_by_user');
		});
	}

}

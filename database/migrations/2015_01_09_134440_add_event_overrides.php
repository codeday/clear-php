<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventOverrides extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		\Schema::table('batches_events', function($table)
		{
			$table->string('name_override')->nullable();
			$table->string('abbr_override')->nullable();
			$table->string('webname_override')->nullable();
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
			$table->dropColumn('name_override');
			$table->dropColumn('abbr_override');
			$table->dropColumn('webname_override');
		});
    }

}

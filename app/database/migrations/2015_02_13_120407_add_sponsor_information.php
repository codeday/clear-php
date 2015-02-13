<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSponsorInformation extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		\Schema::table('batches_events_sponsors', function($table)
		{
			$table->string('blurb');
			$table->text('perk')->nullable();
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
			$table->dropColumn('blurb');
			$table->dropColumn('perk');
		});
    }

}

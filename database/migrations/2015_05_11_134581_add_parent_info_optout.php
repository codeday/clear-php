<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentInfoOptout extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events_registrations', function(Blueprint $table) {
            $table->boolean('parent_no_info')->default(false);
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('batches_events_registrations', function(Blueprint $table) {
            $table->dropColumn('parent_no_info');
        });
    }
}

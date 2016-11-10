<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckedInAt extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events_registrations', function($table)
        {
            $table->datetime('checked_in_at')->nullable();
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
            $table->dropColumn('checked_in_at');
        });
    }

}

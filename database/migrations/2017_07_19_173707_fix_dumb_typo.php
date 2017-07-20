<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixDumbTypo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events_announcements', function (Blueprint $table) {
            $table->dropColumn('batches_events_id');
            $table->string('batches_event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batches_events_announcements', function (Blueprint $table) {
            $table->dropColumn('batches_event_id');
            $table->string('batches_events_id');
        });
    }
}

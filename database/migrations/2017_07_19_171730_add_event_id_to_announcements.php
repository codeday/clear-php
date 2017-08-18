<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventIdToAnnouncements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events_announcements', function (Blueprint $table) {
            $table->string('batches_events_id');
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
            $table->dropColumn('batches_events_id');
        });
    }
}

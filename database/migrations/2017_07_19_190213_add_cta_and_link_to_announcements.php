<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCtaAndLinkToAnnouncements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events_announcements', function (Blueprint $table) {
            $table->string('cta')->nullable();
            $table->string('link')->nullable();
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
            $table->dropColumn('cta');
            $table->dropColumn('link');
        });
    }
}

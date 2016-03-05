<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWaivers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function(Blueprint $table) {
            $table->string('waiver_id');
            $table->dropColumn('waiver_link');
        });

        \Schema::table('batches_events_registrations', function(Blueprint $table) {
            $table->string('waiver_signing_id');
            $table->string('waiver_pdf_link');
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
            $table->dropColumn('waiver_signing_id');
            $table->dropColumn('waiver_pdf_link');
        });

        \Schema::table('batches_events', function(Blueprint $table) {
            $table->dropColumn('waiver_id');
            $table->string('waiver_link');
        });
    }
}

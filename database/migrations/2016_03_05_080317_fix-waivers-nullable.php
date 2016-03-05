<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixWaiversNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE batches_events_registrations MODIFY waiver_signing_id VARCHAR(255) null");
        \DB::statement("ALTER TABLE batches_events_registrations MODIFY waiver_pdf_link VARCHAR(255) null");
        \DB::statement("UPDATE batches_events_registrations SET waiver_signing_id = NULL WHERE waiver_signing_id = ''");
        \DB::statement("UPDATE batches_events_registrations SET waiver_pdf_link = NULL WHERE waiver_pdf_link = ''");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("UPDATE batches_events_registrations SET waiver_signing_id = '' WHERE waiver_signing_id = NULL");
        \DB::statement("UPDATE batches_events_registrations SET waiver_pdf_link = '' WHERE waiver_pdf_link = NULL");
        \DB::statement("ALTER TABLE batches_events_registrations MODIFY waiver_signing_id VARCHAR(255) not null");
        \DB::statement("ALTER TABLE batches_events_registrations MODIFY waiver_pdf_link VARCHAR(255) not null");
    }
}

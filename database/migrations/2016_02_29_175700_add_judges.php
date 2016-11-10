<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddJudges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE batches_events_registrations CHANGE COLUMN type type ENUM('student', 'volunteer', 'mentor', 'judge', 'teacher', 'sponsor', 'vip', 'press')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE batches_events_registrations CHANGE COLUMN type type ENUM('student', 'volunteer', 'mentor', 'teacher', 'sponsor', 'vip', 'press')");
    }
}

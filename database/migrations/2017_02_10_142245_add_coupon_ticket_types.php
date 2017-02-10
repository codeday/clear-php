<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponTicketTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("ALTER TABLE batches_events_promotions CHANGE COLUMN type type ENUM('student', 'volunteer', 'mentor', 'teacher', 'sponsor', 'vip', 'press')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("ALTER TABLE batches_events_promotions CHANGE COLUMN type type ENUM('student', 'volunteer', 'teacher', 'sponsor')");
    }
}

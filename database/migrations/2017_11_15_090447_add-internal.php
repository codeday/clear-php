<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInternal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE batches_events_activities CHANGE COLUMN type type ENUM('workshop', 'speech', 'event', 'internal')");
        DB::statement("ALTER TABLE batches_events_activities MODIFY COLUMN time DECIMAL(4,2)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE batches_events_activities CHANGE COLUMN type type ENUM('workshop', 'speech', 'event')");
        DB::statement("ALTER TABLE batches_events_activities MODIFY COLUMN time DECIMAL(3,1)");
    }
}

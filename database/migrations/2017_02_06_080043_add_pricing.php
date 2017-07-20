<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CodeDay\Clear\Models;

class AddPricing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function(Blueprint $table) {
            $table->float('price_earlybird');
            $table->float('price_regular');
        });
        $events = Models\Batch\Event::withTrashed()->get();

        foreach ($events as $event) {
            $event->price_earlybird = 10;
            $event->price_regular = 20;
            $event->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('batches_events', function(Blueprint $table) {
            $table->dropColumn('price_earlybird');
            $table->dropColumn('price_regular');
        });
    }
}

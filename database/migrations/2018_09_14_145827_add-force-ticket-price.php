<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForceTicketPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE batches_events_promotions MODIFY percent_discount DECIMAL(5,2) NULL');
        Schema::table('batches_events_promotions', function (Blueprint $table) {
            $table->decimal('force_price', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE batches_events_promotions MODIFY percent_discount DECIMAL(5,2) NOT NULL');
        Schema::table('batches_events_promotions', function (Blueprint $table) {
            $table->dropColumn('force_price');
        });
    }
}

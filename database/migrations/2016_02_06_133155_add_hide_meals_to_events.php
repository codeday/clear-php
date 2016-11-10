<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHideMealsToEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events', function (Blueprint $table) {
            $table->boolean('hide_meals')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batches_events', function (Blueprint $table) {
            $table->dropColumn('hide_meals');
        });
    }
}

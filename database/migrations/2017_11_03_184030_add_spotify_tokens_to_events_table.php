<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpotifyTokensToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events', function (Blueprint $table) {
            $table->string('spotify_access_token')->nullable();
            $table->string('spotify_refresh_token')->nullable();
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
            $table->dropColumn('spotify_access_token');
            $table->dropColumn('spotify_refresh_token');
        });
    }
}

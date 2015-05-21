<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhonecalls extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_calls', function(Blueprint $table) {
            $table->increments('id');

            $table->string('batches_event_id');
            $table->text('transcript');
            $table->string('creator_username');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_events_calls');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivities extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_activities', function($table)
        {
            $table->increments('id');

            $table->string('batches_event_id');
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('title');
            $table->enum('type', ['workshop', 'speech', 'event']);
            $table->decimal('time', 3, 1);
            $table->string('url')->nullable();
            $table->string('description')->nullable();

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
        \Schema::drop('batches_events_activities');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailsSent extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('emails_sent', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->enum('to', ['me', 'attendees', 'nonreturning-attendees', 'notify', 'notify-unreg']);
            $table->enum('from', ['me', 'manager', 'codeday']);
            $table->string('subject');
            $table->text('message');

            $table->string('batches_event_id')->nullable();
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

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
        \Schema::drop('emails_sent');
    }

}

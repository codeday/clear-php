<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSpecialLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_speciallinks', function(Blueprint $table){
            $table->increments('id');

            $table->string('batches_event_id');
            $table->string('name');
            $table->string('url');
            $table->boolean('new_window')->default(false);
            $table->enum('location', ['header'])->default('header');

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
        \Schema::drop('batches_events_speciallinks');
    }
}

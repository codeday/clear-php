<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotifyList extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('notify', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->string('batches_event_id')->nullable();
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('set null')->onUpdate('cascade');
            $table->string('region_id')->nullable();
            $table->foreign('region_id')
                ->references('id')->on('regions')
                ->onDelete('set null')->onUpdate('cascade');
            $table->string('batch_id')->nullable();
            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->onDelete('set null')->onUpdate('cascade');

            $table->string('email');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('notify');
    }

}

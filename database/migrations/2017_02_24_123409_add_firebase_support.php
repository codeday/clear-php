<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirebaseSupport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_registrations_devices', function(Blueprint $table) {
            $table->increments('id');
            $table->string('batches_events_registration_id');
            $table->enum('service', ['sms', 'firebase']);
            $table->text('token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_events_registrations_devices');
    }
}

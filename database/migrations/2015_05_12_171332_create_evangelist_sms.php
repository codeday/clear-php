<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvangelistSms extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('evangelist_sms', function(Blueprint $table) {
            $table->increments('id');
            $table->string('content');
			$table->float('hours_offset');
            $table->timestamps();
        });

		\Schema::create('evangelist_sms_sent', function(Blueprint $table) {
			$table->increments('id');
			$table->unsignedInteger('evangelist_sms_id');
            $table->string('batches_event_id');
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
		\Schema::drop('evangelist_sms_sent');
        \Schema::drop('evangelist_sms');
    }

}

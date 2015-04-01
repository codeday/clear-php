<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGiftcards extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('giftcards', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');
            $table->string('code');

			$table->string('batches_events_registration_id')->nullable();

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
        \Schema::dropColumn('giftcards');
    }

}

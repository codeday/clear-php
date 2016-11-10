<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubAccounts extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('users_grants', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->string('username');
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
        \Schema::drop('users_grants');
    }

}

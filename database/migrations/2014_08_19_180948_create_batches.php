<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatches extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('id')->primary();

            $table->string('name');
            $table->boolean('is_loaded');

            $table->datetime('starts_at');
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
        \Schema::drop('batches');
    }

}

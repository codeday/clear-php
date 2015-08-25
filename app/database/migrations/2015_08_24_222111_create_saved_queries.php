<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CodeDay\Clear\Models;

class CreateSavedQueries extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('saved_queries', function($table) {
            $table->increments('id');

            $table->string('name');
            $table->string('description');
            $table->text('sql');

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
        \Schema::drop('saved_queries');
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('regions', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('abbr');

            $table->float('lat');
            $table->float('lng');
            $table->string('timezone');

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
        \Schema::drop('regions');
    }

}

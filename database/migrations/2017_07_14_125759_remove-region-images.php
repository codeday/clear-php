<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveRegionImages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('regions', function(Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('regions', function(Blueprint $table) {
            $table->binary('image');
        });

        \DB::statement('ALTER TABLE `regions` CHANGE `image` `image` LONGBLOB NULL;');
    }
}

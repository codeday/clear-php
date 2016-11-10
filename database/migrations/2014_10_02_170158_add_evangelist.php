<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEvangelist extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function($table)
        {
            $table->string('evangelist_username')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('batches_events', function($table)
        {
            $table->dropColumn('evangelist_username');
        });
    }

}

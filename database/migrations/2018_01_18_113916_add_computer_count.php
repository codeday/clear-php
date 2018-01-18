<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComputerCount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function($table)
        {
            $table->integer('loaners_available')->default(0);
        });

        \Schema::table('batches_events_registrations', function($table)
        {
            $table->boolean('request_loaner')->default(false);
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
            $table->dropColumn('loaners_available');
        });

        \Schema::table('batches_events_registrations', function($table)
        {
            $table->dropColumn('request_loaner');
        });
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxPaid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batches_events_registrations', function (Blueprint $table) {
            $table->decimal('tax_paid', 5, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('batches_events_registrations', function (Blueprint $table) {
            $table->dropColumn('tax_paid', 5, 2);
        });
    }
}

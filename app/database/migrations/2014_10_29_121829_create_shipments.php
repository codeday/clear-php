<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipments extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_shipments', function($table)
        {
            $table->increments('id');

            $table->string('batch_id');
            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->string('batches_event_id')->nullable();
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('item');
            $table->enum('type', ['perbox', 'perparticipant', 'inventory']);
            $table->integer('quantity');

            $table->timestamps();
        });

        \Schema::table('batches_events', function($table)
        {
            $table->string('ship_name')->nullable();
            $table->string('ship_company')->nullable();
            $table->string('ship_address_1')->nullable();
            $table->string('ship_address_2')->nullable();
            $table->string('ship_city')->nullable();
            $table->string('ship_state')->nullable();
            $table->string('ship_postal')->nullable();
            $table->string('ship_country')->nullable();
            $table->boolean('ship_is_residential');

            $table->integer('ship_l')->nullable();
            $table->integer('ship_w')->nullable();
            $table->integer('ship_h')->nullable();
            $table->integer('ship_weight')->nullable();
            $table->integer('shipment_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_shipments');

        \Schema::table('batches_events', function($table)
        {
            $table->dropColumn('ship_name');
            $table->dropColumn('ship_company');
            $table->dropColumn('ship_address_1');
            $table->dropColumn('ship_address_2');
            $table->dropColumn('ship_city');
            $table->dropColumn('ship_state');
            $table->dropColumn('ship_postal');
            $table->dropColumn('ship_country');
            $table->dropColumn('ship_is_residential');

            $table->dropColumn('ship_l');
            $table->dropColumn('ship_w');
            $table->dropColumn('ship_h');
            $table->dropColumn('ship_weight');
            $table->dropColumn('shipment_number');
        });
    }

}

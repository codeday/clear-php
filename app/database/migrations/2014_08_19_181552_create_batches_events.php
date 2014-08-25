<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchesEvents extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('id')->primary();

            $table->string('region_id');
                $table->foreign('region_id')
                    ->references('id')->on('regions')
                    ->onDelete('restrict')->onUpdate('cascade');
            $table->string('batch_id');
                $table->foreign('batch_id')
                    ->references('id')->on('batches')
                    ->onDelete('restrict')->onUpdate('cascade');

            $table->boolean('allow_registrations')->default(false);
            $table->unsignedInteger('registration_estimate')->nullable();
            $table->unsignedInteger('max_registrations')->nullable();

            $table->string('venue_name')->nullable();
            $table->string('venue_address_1')->nullable();
            $table->string('venue_address_2')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_state')->nullable();
            $table->string('venue_postal')->nullable();
            $table->string('venue_country')->nullable();

            $table->string('manager_username')->nullable();

            $table->string('waiver_link')->nullable();

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
        \Schema::drop('batches_events');
    }

}

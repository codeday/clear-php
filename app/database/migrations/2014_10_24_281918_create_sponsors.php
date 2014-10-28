<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSponsors extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_sponsors', function($table)
        {
            $table->increments('id');

            $table->string('batches_event_id');
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('name');
            $table->binary('logo');
            $table->string('url')->nullable();
            $table->string('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        \DB::statement('ALTER TABLE `batches_events_sponsors` CHANGE `logo` `logo` LONGBLOB NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_events_sponsors');
    }

}

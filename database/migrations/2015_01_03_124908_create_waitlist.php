<?php

use \CodeDay\Clear\Models;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWaitlist extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function ($table)
        {
            $table->boolean('allow_waitlist_signups')->default(true);
        });

        foreach (Models\Batch\Event::all() as $event) {
            $event->allow_waitlist_signups = true;
            $event->save();
        }

        \Schema::create('batches_events_waitlist', function ($table) {
            $table->string('id');

            $table->string('batches_event_id');
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('email');

            $table->datetime('offer_sent_at')->nullable();
            $table->datetime('offer_expires_at')->nullable();

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
        \Schema::drop('batches_events_waitlist');
        \Schema::table('batches_events', function ($table)
        {
            $table->dropColumn('allow_waitlist_signups');
        });
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistrations extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('batches_events_promotions', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->string('batches_event_id');
            $table->foreign('batches_event_id')
                ->references('id')->on('batches_events')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->string('code');
            $table->text('notes');

            $table->decimal('percent_discount', 5, 2);
            $table->date('expires_at')->nullable();
            $table->integer('allowed_uses')->nullable();

            $table->timestamps();
        });

        \Schema::create('batches_events_registrations', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->string('batches_event_id');
                $table->foreign('batches_event_id')
                    ->references('id')->on('batches_events')
                    ->onDelete('restrict')->onUpdate('cascade');

            $table->string('stripe_id')->nullable();
            $table->decimal('amount_paid', 5, 2);
            $table->unsignedInteger('batches_events_promotion_id')->nullable();
                $table->foreign('batches_events_promotion_id')
                    ->references('id')->on('batches_events_promotions')
                    ->onDelete('restrict')->onUpdate('cascade');

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->integer('age')->nullable();

            $table->boolean('share_with_sponsors');

            $table->boolean('is_checked_in')->default(false);

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
        \Schema::drop('batches_events_registrations');
        \Schema::drop('batches_events_promotions');
    }

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CodeDay\Clear\Models;

class CreateAgreements extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('agreements', function($table) {
            $table->increments('id');

            $table->string('name');
            $table->longText('html')->nullable();
            $table->longText('markdown')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        \Schema::table('batches_events', function ($table) {
            $table->unsignedInteger('agreement_id')->nullable();
            $table->string('agreement_signing_id')->nullable();
            $table->string('agreement_signed_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('agreements')
        ;
        \Schema::table('batches_events', function ($table) {
            $table->dropColumn('agreement_id');
            $table->dropColumn('agreement_signing_id');
            $table->dropColumn('agreement_signed_url');
        });
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSurveyEmail extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches', function($table)
        {
            $table->datetime('survey_email_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('batches', function($table)
        {
            $table->dropColumn('survey_email_sent_at');
        });
    }
}

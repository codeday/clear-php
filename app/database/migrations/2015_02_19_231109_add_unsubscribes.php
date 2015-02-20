<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnsubscribes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		\Schema::create('unsubscribes', function(\Illuminate\Database\Schema\Blueprint $table) {
			$table->increments('id');
			$table->string('email');
			$table->enum('type', ['marketing', 'all']);

			$table->timestamps();
		});

        \Schema::table('email_templates', function($table)
        {
            $table->boolean('is_marketing')->default(false);
        });

        \Schema::table('emails_sent', function($table)
        {
            $table->boolean('is_marketing')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('unsubscribes');


        \Schema::table('email_templates', function($table)
        {
            $table->dropColumn('is_marketing');
        });

        \Schema::table('emails_sent', function($table)
        {
            $table->dropColumn('is_marketing');
        });
    }

}

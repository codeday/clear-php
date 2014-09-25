<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailTemplates extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('email_templates', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->enum('to', ['me', 'attendees', 'notify', 'notify-unreg']);
            $table->enum('from', ['me', 'manager', 'studentrnd']);
            $table->string('subject');
            $table->text('message');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('email_templates');
    }

}

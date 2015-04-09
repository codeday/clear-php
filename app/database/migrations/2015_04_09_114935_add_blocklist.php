<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBlocklist extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		\Schema::create('bans', function(\Illuminate\Database\Schema\Blueprint $table) {
			$table->increments('id');

			$table->string('first_name');
			$table->string('last_name');
			$table->string('email');

			$table->date('expires_at')->nullable();
			$table->enum('reason',
				['harassment', 'drugs', 'weapons', 'codeofconduct',
				 'chargeback', 'noshow', 'young', 'old', 'recruiter', 'other']);
			$table->text('details');

			$table->string('created_by_username');

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
        \Schema::drop('bans');
    }

}

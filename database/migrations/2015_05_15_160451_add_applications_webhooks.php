<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplicationsWebhooks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		\Schema::create('applications_webhooks', function(\Illuminate\Database\Schema\Blueprint $table) {
			$table->string('id')->primary();

			$table->string('application_id');
				$table->foreign('application_id')
					->references('public')->on('applications')
					->onDelete('restrict')->onUpdate('cascade');

			$table->string('url');
			$table->string('event');

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
		\Schema::drop('applications_webhooks');
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApplications extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::create('applications', function(\Illuminate\Database\Schema\Blueprint $table) {
            $table->string('public')->primary();
            $table->string('private');

            $table->string('name');
            $table->text('description');

			$table->boolean('permission_admin');
            $table->boolean('permission_internal');

			$table->string('admin_username');

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
        \Schema::drop('applications');
    }

}

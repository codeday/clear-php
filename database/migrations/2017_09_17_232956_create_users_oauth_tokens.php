<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersOauthTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_oauth_tokens', function (Blueprint $table) {
            $table->string("user_username");
            $table->string("application_token");
            $table->string("token");
            $table->string("access_token");
            $table->boolean("access_token_used");
            $table->string("scopes");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users_oauth_tokens');
    }
}

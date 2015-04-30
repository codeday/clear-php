<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCertifiedEvangelist extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('users', function($table)
        {
            $table->boolean('is_certified_evangelist')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('users', function($table)
        {
            $table->dropColumn('is_certified_evangelist');
        });
    }

}

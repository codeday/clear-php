<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpsShippingTracking extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		\Schema::table('batches_events', function($table)
		{
			$table->string('shipment_tracking')->nullable();
			$table->binary('shipment_label')->nullable();

            $table->dropColumn('ship_l');
            $table->dropColumn('ship_w');
            $table->dropColumn('ship_h');
            $table->dropColumn('ship_weight');
            $table->dropColumn('shipment_number');
		});

        \DB::statement('ALTER TABLE `batches_events` CHANGE `shipment_label` `shipment_label` LONGBLOB NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('batches_events', function($table)
        {
            $table->dropColumn('shipment_tracking');
            $table->dropColumn('shipment_label');

            $table->integer('ship_l')->nullable();
            $table->integer('ship_w')->nullable();
            $table->integer('ship_h')->nullable();
            $table->integer('ship_weight')->nullable();
            $table->integer('shipment_number')->nullable();
		});
    }

}

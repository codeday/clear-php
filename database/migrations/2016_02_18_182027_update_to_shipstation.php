<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateToShipstation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('batches_events', function(Blueprint $table) {
            $table->dropColumn('shipment_tracking');
            $table->dropColumn('shipment_label');
            $table->string('shipstation_id')->nullable();
        });
        \Schema::table('batches_supplies', function($table) {
            $table->string('sku');
            $table->decimal('weight', 5, 2);
        });
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
			$table->string('shipment_tracking')->nullable();
            $table->binary('shipment_label')->nullable();
            $table->dropColumn('shipstation_id');
		});

        \DB::statement('ALTER TABLE `batches_events` CHANGE `shipment_label` `shipment_label` LONGBLOB NULL;');
        
        \Schema::table('batches_supplies', function($table) {
            $table->dropColumn('sku');
            $table->dropColumn('weight');
        });
    }
}

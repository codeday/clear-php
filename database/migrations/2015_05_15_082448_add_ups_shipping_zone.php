<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \CodeDay\Clear\Models;

class AddUpsShippingZone extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('regions', function(Blueprint $table) {
            $table->integer('ground_days_in_transit');
        });

        $days_in_transit = [
            'seattle'       => 1,
            'portland'      => 1,
            'corvallis'     => 1,
            'sf'            => 2,
            'sv'            => 2,
            'la'            => 3,
            'sandiego'      => 3,
            'vegas'         => 3,
            'slc'           => 2,
            'phoenix'       => 3,
            'boulder'       => 3,
            'dallas'        => 4,
            'austin'        => 5,
            'houston'       => 5,
            'omaha'         => 4,
            'kansascity'    => 4,
            'desmoines'     => 4,
            'minneapolis'   => 4,
            'wisconsin'     => 4,
            'chicago'       => 4
        ];

        foreach (Models\Region::get() as $region) {
            if (isset($days_in_transit[$region->id])) {
                $region->ground_days_in_transit = $days_in_transit[$region->id];
            } else {
                $region->ground_days_in_transit = 5;
            }

            $region->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       \Schema::table('regions', function(Blueprint $table) {
           $table->dropColumn('ground_days_in_transit');
       });
    }

}

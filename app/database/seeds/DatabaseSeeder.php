<?php

use \CodeDay\Clear\Models;

class DatabaseSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();

        // Add regions
        $regions = explode("\n", file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'regions.csv'));
        foreach ($regions as $region) {
            list($name, $webname, $abbr, $timezone, $lat, $lng) = explode(',', $region);
            if (!Models\Region::find($webname)) {
                $region = new Models\Region;

                $region->id = $webname;
                $region->name = $name;
                $region->abbr = $abbr;
                $region->timezone = $timezone;
                $region->lat = $lat;
                $region->lng = $lng;

                $region->save();
            }
        }
	}

}

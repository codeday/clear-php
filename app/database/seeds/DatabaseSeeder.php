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

        // Add batches
        if (!Models\Batch::exists()) {
            $past_batch = new Models\Batch;
            $past_batch->starts_at = time() - (60 * 60 * 24 * 30);
            $past_batch->name = "Past";
            $past_batch->is_loaded = false;
            $past_batch->save();

            $present_batch = new Models\Batch;
            $present_batch->starts_at = time() + (60 * 60 * 24 * 365 * 10);
            $present_batch->name = "Far Future";
            $present_batch->is_loaded = true;
            $present_batch->save();
        }

        $batch = Models\Batch::Loaded();

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

                // Add an event in this region
                $event = new Models\Batch\Event;
                $event->region_id = $webname;
                $event->batch_id = $batch->id;
                $event->manager_username = null;
                $event->registration_estimate = 100;
                $event->save();
            }
        }

	}

}

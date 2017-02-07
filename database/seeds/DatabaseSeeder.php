<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use \CodeDay\Clear\Models;

class DatabaseSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
        Model::unguard();

        // Add batches
        if (!Models\Batch::exists()) {
						$present_batch = new Models\Batch;
						$present_batch->starts_at = time() + (60 * 60 * 24 * 365 * 10);
						$present_batch->name = "Far Future";
                        $present_batch->is_loaded = true;
                        $present_batch->allow_registrations = true;
						$present_batch->save();
						
            $past_batch = new Models\Batch;
            $past_batch->starts_at = time() - (60 * 60 * 24 * 30);
            $past_batch->name = "Past";
            $past_batch->is_loaded = false;
            $past_batch->save();
        }

        $batch = Models\Batch::Loaded();

        // Add regions
        $regions = explode("\n", file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'regions.csv'));
        foreach ($regions as $region) {
            if (rand(0,10) < 6) continue;
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
                $event->venue_name = "StudentRND";
                $event->venue_address_1 = "425 15th Ave E";
                $event->venue_city = "Seattle";
                $event->venue_state = "WA";
                $event->venue_postal = "98102";
                $event->venue_country = "US";
                $event->venue_contact_first_name = "Tyler";
                $event->venue_contact_last_name = "Menezes";
                $event->venue_contact_email = "tylermenezes@srnd.org";
                $event->venue_contact_phone = "1886077763";
                $event->max_registrations = 100;
                $event->allow_registrations = rand(0,10) < 8;
                $event->save();

                if ($event->allow_registrations) {
                    $attendees = rand(60,100);
                    for($i = 0; $i < $attendees; $i++) {
                        $attendee = new Models\Batch\Event\Registration;
                        $attendee->id = \str_random(10);
                        $attendee->first_name = ucfirst($this->randWord());
                        $attendee->last_name = ucfirst($this->randWord());
                        $attendee->amount_paid = 10;
                        $attendee->email = "null@localhost.localhost";
                        $attendee->type = rand(0,10) < 7 ? 'student' : array_rand(['volunteer', 'mentor', 'judge', 'vip']);
                        if (rand(0,10) < 8) {
                            $attendee->age = rand(15,30);
                            if ($attendee->age >= 18) {
                                $attendee->parent_no_info = true;
                            } else {
                                $attendee->parent_name = "Tyler Menezes";
                                $attendee->parent_email = "null@localhost.localhost";
                                $attendee->parent_phone = "2067394741";
                            }
                            if (rand(0,10) < 5) {
                                $attendee->waiver_pdf_link = "https://example.com/";
                            }
                        }
                        $attendee->batches_event_id = $event->id;
                        $attendee->save();
                    }
                }
            }
        }

        // Add test application
        if (!Models\Application::where('public', '=', 'testtesttesttesttesttest')->exists()) {
            $app = new Models\Application;
            $app->name = 'Test App';
            $app->description = 'Internal test';
            $app->public = 'testtesttesttesttesttest';
            $app->private = 'testtesttesttesttesttest';
            $app->permission_admin = false;
            $app->permission_internal = false;
            $app->save();

            // Overridden by creating method in model
            $app->public = 'testtesttesttesttesttest';
            $app->private = 'testtesttesttesttesttest';
            $app->save();
        }
    }

    private function randWord() {
        $handle = @fopen("/usr/share/dict/words", "r");
        if ($handle) {
            $random_line = null;
            $line = null;
            $count = 0;
            while (($line = fgets($handle, 4096)) !== false) {
                $count++;
                // P(1/$count) probability of picking current line as random line
                if(rand() % $count == 0) {
                  $random_line = $line;
                }
            }
            if (!feof($handle)) {
                echo "Error: unexpected fgets() fail\n";
                fclose($handle);
                return null;
            } else {
                fclose($handle);
            }
            return $random_line;
        }
    }

}

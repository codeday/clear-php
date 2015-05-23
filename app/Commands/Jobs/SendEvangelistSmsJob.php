<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SendEvangelistSmsJob {
    public $interval = 'always';//'6 minutes';
    public function fire()
    {
        foreach (Models\Batch::Loaded()->events as $event) {
            if (!$event->evangelist_username || !$event->evangelist->phone) continue;

            $adjustedBatchTimezone = Models\Batch::Loaded()->starts_at->setTimezone($event->region->timezone)->hour(12);
            foreach (Models\EvangelistSms::all() as $sms) {
                if ($adjustedBatchTimezone->copy()->addHours($sms->hours_offset)->isPast()
                    && !$sms->wasSentToEvent($event)) {

                    try {
                        echo strtoupper($event->name).': '
                            .$adjustedBatchTimezone->copy()->addHours($sms->hours_offset)->toRfc850String()
                            .' '.$sms->content."\n";
                        Services\Sms::sendToUser($event->evangelist, $sms->content);
                    } catch (\Exception $ex) { print $event->name.": ".$ex->getMessage()."\n"; }

                    $sent = new Models\EvangelistSms\Sent;
                    $sent->batches_event_id = $event->id;
                    $sent->evangelist_sms_id = $sms->id;
                    $sent->save();
                }
            }
        }
    }
}
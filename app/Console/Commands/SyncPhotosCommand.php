<?php
namespace CodeDay\Clear\Console\Commands;

use CodeDay\Clear\Models;
use CodeDay\Clear\Jobs;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SyncPhotosCommand extends Command {
    protected $name = 'sync:photos';
    protected $description = 'Syncs all attendee photos from FullContact';

    public function fire()
    {
        foreach (Models\Batch::Loaded()->events as $event)
        {
            foreach ($event->registrations as $reg)
            {
                if (!isset($reg->profile_image)) {
                    echo $reg->email."\n";
                    try {
                        (new Jobs\SyncProfileImageJob($reg))->handle();
                    } catch (\Exception $ex) { echo $ex->getMessage(); }
                    usleep(250000);
                }
            }
        }
    }
}

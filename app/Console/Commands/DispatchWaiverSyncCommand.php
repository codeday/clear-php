<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use CodeDay\Clear\Models;
use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Jobs;

class DispatchWaiverSyncCommand extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:waiver-sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches all waiver sync jobs.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $outstandingWaivers = Event\Registration
            ::select('batches_events_registrations.*')
            ->join('batches_events', 'batches_events.id', '=', 'batches_events_registrations.batches_event_id')
            ->where('batches_events.batch_id', '=', Models\Batch::Loaded()->id)
            ->whereNotNull('batches_events_registrations.waiver_signing_id')
            ->whereNull('batches_events_registrations.waiver_pdf_link')
            ->get();

        foreach ($outstandingWaivers as $reg) {
            $this->dispatch(new Jobs\SyncWaiverForJob($reg));
        }
    }
}

<?php

namespace CodeDay\Clear\Jobs;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use CodeDay\Clear\Models;
use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Services;
use Carbon\Carbon;

class SyncWaiverForJob extends Job
{
    use DispatchesJobs, SerializesModels;
    protected $reg;

    public function __construct(Event\Registration $reg)
    {
        $this->reg = $reg;
    }

    public function handle()
    {
        Services\Waiver::sync($this->reg);
    }
}

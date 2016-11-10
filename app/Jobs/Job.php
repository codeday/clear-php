<?php

namespace CodeDay\Clear\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

abstract class Job implements SelfHandling, ShouldQueue
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable;
}

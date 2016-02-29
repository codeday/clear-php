<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use CodeDay\Clear\Jobs;

class DispatchTransactionalEmailsCommand extends Command
{
    use DispatchesJobs;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch:transactional-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatches all transactional emails.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        (new Jobs\SendTransactionalEmailsJob())->handle();
        //$this->dispatch(new Jobs\SendTransactionalEmailsJob());
    }
}

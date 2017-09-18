<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use CodeDay\Clear\Models;

class PurgeRegistrationsCommand extends Command {
    protected $name = 'registrations:purge';
    protected $description = 'Purge all registrations. This command is dangerous.';

    public function fire()
    {
        if ($this->confirm('Are you sure you want to run this command? It will delete EVERY REGISTRATION. EVERY. SINGLE. ONE. [yes|no]'))
        {
            if ($this->confirm('Just to make sure you didn\'t accidentally type yes, please confirm again that you would indeed like to delete EVERY REGISTRATION from the ENTIRE DATABASE for EVERY EVENT from EVERY SEASON. [yes|no]'))
            {
                if ($this->confirm('You\'re sure? [yes|no]'))
                {
                    $this->info('Great. Not my fault if you didn\'t want to do this. I warned you!');
                    Models\Batch\Event\Registration::truncate();
                }
            }
        }
    }
}

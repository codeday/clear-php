<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use CodeDay\Clear\Models;
use Symfony\Component\Console\Input\InputArgument;
use CodeDay\Clear\Services;

class DispatchEmailManuallyCommand extends Command {
    protected $name = 'dispatch:manual-email';
    protected $description = 'Dispatches an email to a registration manually.';

    public function getArguments()
    {
        return array(
            array('registration', InputArgument::REQUIRED, 'The id of the registration to send the email to.'),
            array('template', InputArgument::REQUIRED, 'The template of the email to send.')
        );
    }

    public function fire()
    {
        $registration = Models\Batch\Event\Registration::findOrFail($this->argument('registration'));        
        Services\Email::SendOnQueue(
          "CodeDay",
          "me@tjhorner.com",
          $registration->name,
          $registration->email,
          "CodeDay Test Email 2",
          "lol",
          \View::make('emails/' . $this->argument('template'), ['registration' => $registration])
        );
    }
}

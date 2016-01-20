<?php
namespace CodeDay\Clear\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\NullOutput;

class TestCommand extends Command {
    use \Illuminate\Console\ConfirmableTrait;

    protected $name = 'test';
    protected $description = 'Runs tests.';

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run.'],
            ['fast', null, InputOption::VALUE_NONE, 'Skip clearing database and queue. Test results not guaranteed.'],
            ['teamcity', null, InputOption::VALUE_NONE, 'Output TeamCity service messages.']
        ];
    }

    public function fire()
    {
        if ( ! $this->confirmToProceed('This Command Will Flush the Database!', function(){ return true; })) return;

        if ($this->option('teamcity')) {
            $this->output = new NullOutput;
        }

        $realOutput = $this->output; // We'll set the output to NullOutput before doing any artisan commands, since
                                     // that's the only way to suppress output, but we'll use the correct output again
                                     // whenever we want to output any real text.

        if (!$this->option('fast')) {
            $this->comment("Preparing the database:");

            $this->comment('... dropping database.');
            $this->output = new NullOutput();
            $this->call('migrate:reset', ['--force' => true]);;
            $this->output = $realOutput;

            $this->comment('... creating database.');
            $this->output = new NullOutput();
            $this->call('migrate', ['--force' => true]);;
            $this->output = $realOutput;

            $this->comment("... seeding.");
            $this->call('db:seed', ['--force' => true]);


            $this->comment("Clearing the queue server.");
            $this->output = new NullOutput();
            $this->call('queue:beanstalkd:clear');
            $this->output = $realOutput;
        }

        $this->comment("Running PHPUnit tests:");
        $result = null;

        if ($this->option('teamcity')) {
            $command = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Tests', 'phpunit-teamcity.php']);
        } else {
            $command = implode(DIRECTORY_SEPARATOR,
                [dirname(dirname(__DIR__)), 'vendor', 'phpunit', 'phpunit', 'phpunit']);
        }

        system('php "'.$command.'"', $result);
        return $result;
    }
}
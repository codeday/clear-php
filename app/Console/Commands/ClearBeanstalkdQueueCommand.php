<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearBeanstalkdQueueCommand extends Command {
    protected $name = 'queue:beanstalkd:clear';
    protected $description = 'Clear a Beanstalkd queue, by deleting all pending jobs.';

    public function getArguments()
    {
        return array(
            array('queue', InputArgument::OPTIONAL, 'The name of the queue to clear.'),
        );
    }

    public function fire()
    {
        $queue = ($this->argument('queue')) ? $this->argument('queue') : \Config::get('queue.connections.beanstalkd.queue');
        $this->info(sprintf('Clearing queue: %s', $queue));
        $pheanstalk = \Queue::getPheanstalk();
        $pheanstalk->useTube($queue);
        $pheanstalk->watch($queue);
        while ($job = $pheanstalk->reserve(0)) {
            $pheanstalk->delete($job);
        }
        $this->info('...cleared.');
    }
}

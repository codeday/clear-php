<?php
namespace CodeDay\Clear\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use \CodeDay\Clear\Models;
use \Carbon\Carbon;

class CronCommand extends Command {
    protected $name = 'cron';
    protected $description = 'Runs all pending cronjobs.';

    public function fire()
    {
        foreach (glob(implode(DIRECTORY_SEPARATOR, [__DIR__, 'Jobs', "*.php"])) as $filename) {
            include_once($filename);

            $className = basename($filename, ".php");
            $fqClassName = '\\CodeDay\\Clear\\Commands\\Jobs\\'.$className;
            $instance = new $fqClassName();

            $cronjob = Models\Cronjob::where('class', '=', $fqClassName)->first();
            if (!$cronjob) {
                $cronjob = new Models\Cronjob;
                $cronjob->class = $fqClassName;
            }

            $runInterval = $this->intDurationFromString($instance->interval);
            $nextRun = ($cronjob->updated_at ? $cronjob->updated_at->copy()->addSeconds($runInterval)
                                 : Carbon::createFromTimestamp(0));

            if (Carbon::now()->gte($nextRun)) {
                $this->comment($className.' - running!');
                $instance->fire();
                $cronjob->touch();
            } else {
                $this->comment($className.' - not scheduled to run (next run '.$nextRun->toRfc850String().')');
            }
        }
    }

    private function intDurationFromString($stringDuration)
    {
        if ($stringDuration === 'always') {
            return 0;
        }

        list($amount, $unit) = explode(' ', $stringDuration);
        if (substr($unit, -1) === 's') {
            $unit = substr($unit, 0, strlen($unit) - 1);
        }

        $unitBaseDuration = 0;
        switch ($unit) {
            case "second":
                $unitBaseDuration = 1;
                break;
            case "minute":
                $unitBaseDuration = 60;
                break;
            case "hour":
                $unitBaseDuration = 60*60;
                break;
            case "day":
                $unitBaseDuration = 60*60*24;
                break;
            case "week":
                $unitBaseDuration = 60*60*24*7;
                break;
        }

        return $amount * $unitBaseDuration;
    }
}

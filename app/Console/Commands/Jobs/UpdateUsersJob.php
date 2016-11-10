<?php

namespace CodeDay\Clear\Commands\Jobs;

use \Carbon\Carbon;
use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class UpdateUsersJob {
    public $interval = '2 hours';
    public function fire()
    {
        foreach (Models\User::get() as $user) {
            $username = $user->username;
            echo "Queuing update for $username\n";
            \Queue::push(function ($job) use ($username) {
                $user = Models\User::where('username', '=', $username)->first();
                $user->s5Update();
                $job->delete();
            });
        }
    }
}
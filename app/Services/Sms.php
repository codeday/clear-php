<?php
namespace CodeDay\Clear\Services;

use \CodeDay\Clear\Models;

class Sms {
    public static function sendToList($list, $message, $from = null)
    {
        if (!in_array($list, ['evangelists', 'rms'])) { throw new \Exception('List type '.$list.' unsupported.'); }

        $batch = Models\Batch::Managed() ? Models\Batch::Managed() : Models\Batch::Loaded();

        foreach ($batch->events as $event) {
            if ($list === 'evangelists' && $event->evangelist_username && $event->evangelist->phone) {
                self::sendToUser($event->evangelist, $message, $from);
            } elseif ($list === 'rms' && $event->manager_username && $event->manager->phone) {
                self::sendToUser($event->manager, $message, $from);
            }
        }
    }

    public static function sendToUser(Models\User $user, $message, $from = null)
    {
        if (!$user->phone) {
            throw new \Exception ("No phone number set for ".$user->username);
        }

        if ($user->sms_optout) { return; }

        self::send($user->phone, $message, $from);
    }

    public static function send($toNumber, $message, $from = null)
    {
        if ($from === null) { $from = \Config::get('twilio.from'); }

        \Queue::push(function($job) use ($toNumber, $message, $from) {
            $client = new \Services_Twilio(\Config::get('twilio.sid'), \Config::get('twilio.token'));

            $message = $client->account->messages->create(array(
                "From" => $from,
                "To" => $toNumber,
                "Body" => $message,
            ));

            $job->delete();
        });
    }
}
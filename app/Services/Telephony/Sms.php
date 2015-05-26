<?php
namespace CodeDay\Clear\Services\Telephony;

use \CodeDay\Clear\Models;

/**
 * Helps with sending SMS to phone numbers.
 *
 * @package CodeDay\Clear\Services\Telephony
 */
class Sms {

    /**
     * Sends an email to the
     *
     * @param string        $list           The list to send the message to, one of:
     *                                        - evangelists
     *                                        - rms
     * @param string        $message        The message to send
     * @param string|null   $from           The number the message should appear to come from
     *
     * @throws \Exception                   Exception if the list type is not set
     */
    public static function sendToList($list, $message, $from = null) // TODO: This should take a batch as input
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

    /**
     * Sends an SMS to a specified user.
     *
     * @param Models\User   $user           The user to send the message to
     * @param string        $message        The message to send
     * @param string|null   $from           The number the message should appear to come from
     *
     * @throws \Exception                   Exception if the user does not have a number set
     */
    public static function sendToUser(Models\User $user, $message, $from = null)
    {
        if (!$user->phone) {
            throw new \Exception ("No phone number set for ".$user->username);
        }

        if ($user->sms_optout) { return; }

        self::send($user->phone, $message, $from);
    }

    /**
     * Sends an SMS to a number.
     *
     * @param string        $toNumber       The number to send the message to
     * @param string        $message        The message to send
     * @param string|null   $from           The number the message should appear to come from
     */
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
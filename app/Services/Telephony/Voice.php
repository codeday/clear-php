<?php
namespace CodeDay\Clear\Services\Telephony;

use \CodeDay\Clear\Models;

/**
 * Helps with making phone calls.
 *
 * Manages the sending of phone calls to participants and other numbers. Supports features such as connecting two
 * users and robo-calling. Offloads all calls to a queue for better processing.
 *
 * @package CodeDay\Clear\Services\Telephony
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Voice {

    /**
     * Calls all parents at the provided event and, upon their answer, executes the provided Twiml.
     *
     * @param Models\Batch\Event    $event      The event to call all parents at
     * @param string                $twiml      The Twiml to execute when the parent picks up
     * @param string|null           $from       Fully-qualified phone number of the sender
     */
    public static function callEventParents(Models\Batch\Event $event, $twiml, $from = null)
    {
        foreach ($event->registrations as $registrant) {
            try {
                self::callRegistrantParents($registrant, $twiml, $from);
            } catch (\Exception $ex) {}
        }
    }

    /**
     * Calls all registered phones for a parent (both primary and secondary, if both exist) and, upon their answer,
     * executes the provided Twiml.
     *
     * @param Models\Batch\Event\Registration   $registrant     The registrant whose parents you want to call
     * @param string                            $twiml          The Twiml to execute when the parent picks up
     * @param string|null                       $from           Fully-qualified phone number of the sender
     *
     * @throws \Exception
     */
    public static function callRegistrantParents(Models\Batch\Event\Registration $registrant, $twiml, $from = null)
    {
        $callsPlaced = 0;
        foreach ([$registrant->parent_phone, $registrant->parent_secondary_phone] as $phoneNumber) {
            if ($phoneNumber) {
                $callsPlaced++;
                self::call($phoneNumber, $twiml, $from);
            }
        }

        if (!$callsPlaced) {
            throw new \Exception ("No parent phone number set for ".$registrant->name);
        }
    }

    /**
     * Calls a number and, upon their answer, executes the provided Twiml.
     *
     * @param $toNumber
     * @param $twiml
     * @param null $from
     */
    public static function call($toNumber, $twiml, $from = null)
    {
        if ($from === null) { $from = \Config::get('twilio.from'); }

        $toNumber = self::sanitizeNumber($toNumber);
        $from = self::sanitizeNumber($from);

        \Queue::push(function($job) use ($toNumber, $twiml, $from) {
            $client = new \Services_Twilio(\Config::get('twilio.sid'), \Config::get('twilio.token'));

            $client->account->calls->create(
                $from,
                $toNumber,
                'http://twimlets.com/echo?Twiml='.urlencode($twiml)
            );

            $job->delete();
        });
    }

    /**
     * Calls the first number and, upon their answer, connects them to the second number.
     *
     * @param string        $first                  Phone number to call first
     * @param string        $second                 Phone number to connect the first number to after the first answers
     * @param string|null   $from                   Number the call should appear to be from
     * @param string|null   $preConnectionTwiml     Twiml to run after the first person connects, but before calling
     *                                              the second.
     */
    public static function connectPhones($first, $second, $from = null, $preConnectionTwiml = null)
    {
        if ($from === null) { $from = \Config::get('twilio.from'); }
        if ($preConnectionTwiml === null) {
            $preConnectionTwiml = "<Say voice=\"alice\">I am now connecting you.</Say>";
        }

        $first = self::sanitizeNumber($first);
        $second = self::sanitizeNumber($second);
        $from = self::sanitizeNumber($from);

        $twiml = '<Response>'.$preConnectionTwiml.'<Dial>'.$second.'</Dial></Response>';

        \Queue::push(function($job) use ($first, $from, $twiml) {
            $client = new \Services_Twilio(\Config::get('twilio.sid'), \Config::get('twilio.token'));

            $client->account->calls->create(
                $from,
                $first,
                'http://twimlets.com/echo?Twiml='.urlencode($twiml)
            );

            $job->delete();
        });
    }

    /**
     * Takes an input string and tries to format it as a proper phone number.
     *
     * @param string            $number         The number to format
     * @return null|string                      An 11-digit number with no punctuation, or null if no number found
     */
    public static function sanitizeNumber($number)
    {
        $cleaned = preg_replace('/\D/', '', $number);
        if (strlen($cleaned) === 10) {
            return '1'.$number;
        } elseif (strlen($cleaned) === 11) {
            return $number;
        } else {
            return null;
        }
    }
}
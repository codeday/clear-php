<?php
namespace CodeDay\Clear\Services;

use \CodeDay\Clear\Models;

class Phone {

    public static function callEventParents(Models\Batch\Event $event, $twiml, $from = null)
    {
        foreach ($event->registrations as $registrant) {
            try {
                self::callRegistrantParents($registrant, $twiml, $from);
            } catch (\Exception $ex) {}
        }
    }

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

    public static function connectPhones($first, $second, $from = null)
    {
        if ($from === null) { $from = \Config::get('twilio.from'); }

        $first = self::sanitizeNumber($first);
        $second = self::sanitizeNumber($second);
        $from = self::sanitizeNumber($from);

        $twiml = '<Response><Say>Connecting you.</Say><Dial>'.$second.'</Dial></Response>';

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

    private static function sanitizeNumber($number)
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
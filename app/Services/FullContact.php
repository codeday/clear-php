<?php
namespace CodeDay\Clear\Services;

use CodeDay\Clear\Models\Batch\Event;
use GuzzleHttp;

class FullContact
{
    protected static $client;

    public static function getDataFor(Event\Registration $registration)
    {
        $response = self::$client->get('https://api.fullcontact.com/v2/person.json', ['query' => [
            'email' => $registration->email,
            'apiKey' => \Config::get('fullcontact.api_key')
        ]]);

        if ($response->getStatusCode() == 202) {
            return false;
        } elseif ($response->getStatusCode() != 200) {
            return null;
        } else {
            return json_decode($response->getBody());
        }
    }


    public static function booting()
    {
        self::$client = new GuzzleHttp\Client([]);
    }
}
FullContact::booting();

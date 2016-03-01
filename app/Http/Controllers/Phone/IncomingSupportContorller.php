<?php
namespace CodeDay\Clear\Http\Controllers\Phone;

use \CodeDay\Clear\Models;
use \Carbon\Carbon;

class IncomingSupportController extends \CodeDay\Clear\Http\Controller {
    private function getEventsByTimezone()
    {
        $tz_regions = ['America/Los_Angeles' => [], 'America/Denver' => [], 'America/Chicago' => [], 'America/Detroit' => []];
        foreach (Models\Batch::Loaded()->events as $event) {
            if (!$event->evangelist_username) continue;
            $tz_regions[$event->region->timezone][] = $event;
        }

        return $tz_regions;
    }

    public function getIndex()
    {
        $callerRegistration =
            Models\Batch\Event\SupportCall::getRegistrationFromParentPhoneNumber(\Input::get('Caller'));
        if ($callerRegistration) {
            return $this->connect($callerRegistration->event);
        }

        $xml = '<Response>';
        $xml .= '<Gather numDigits="1" action="/phone/support/region" method="GET">';
        $xml .= '<Play>/assets/mp3/phonetimezones.mp3</Play>';
        $xml .= '<Pause length="4" />';
        $xml .= '<Play>/assets/mp3/phonetimezones.mp3</Play>';
        $xml .= '</Gather>';
        $xml .= '</Response>';

        $response = \Response::make($xml, 200);
        $response->header('Content-type', 'text/xml');
        return $response;
    }

    public function getRegion()
    {
        $region_index = \Input::get('Digits');

        $regions = [
            'America/Los_Angeles',
            'America/Denver',
            'America/Chicago',
            'America/Detroit'
        ];

        // Check if the region was valid
        if ($region_index < 1 || $region_index > count($regions)) {
            $response = \Response::make(
                '<Response><Say voice="alice">Please select from one of the region options.</Say>'
                .'<Redirect method="GET">/phone/support</Redirect></Response>', 200);
            $response->header('Content-type', 'text/xml');
            return $response;
        }

        $events = $this->getEventsByTimezone()[$regions[$region_index - 1]];

        $xml = '<Response>';
        $xml .= '<Gather numDigits="1" action="/phone/support/event?region='.$region_index.'" method="GET">';

        $i = 0;
        foreach ($events as $event) {
            $i++;
            $xml .= '<Play>/assets/mp3/phonespeakto.mp3</Play>';
            $xml .= '<Say voice="alice">'.$event->name.'</Say>';
            $xml .= '<Play>/assets/mp3/phonepress.mp3</Play>';
            $xml .= '<Say voice="alice">'.$i.'</Say>';
            $xml .= '<Pause length="1" />';
        }

        $xml .= '</Gather>';
        $xml .= '<Redirect method="GET"></Redirect>';
        $xml .= '</Response>';

        $response = \Response::make($xml, 200);
        $response->header('Content-type', 'text/xml');
        return $response;
    }

    public function getEvent()
    {
        $region_index = \Input::get('region');
        $event_index = \Input::get('Digits');

        $regions = [
            'America/Los_Angeles',
            'America/Denver',
            'America/Chicago',
            'America/Detroit'
        ];
        $events = $this->getEventsByTimezone()[$regions[$region_index - 1]];

        // Check if the event was valid
        if (!$event_index || !isset($events[$event_index - 1])) {
            $response = \Response::make(
                '<Response><Say voice="alice">Please select from one of the event options.</Say>'
                . '<Redirect method="GET">/phone/support/region?Digits=' . $region_index . '</Redirect></Response>', 200);
            $response->header('Content-type', 'text/xml');
            return $response;
        }

        $event = $events[$event_index - 1];
        return $this->connect($event);
    }

    public function connect($event)
    {
        $supportCall = new Models\Batch\Event\SupportCall;
        $supportCall->caller = \Input::get('Caller');
        $supportCall->call_sid = \Input::get('CallSid');
        $supportCall->batches_event_id = $event->id;

        $routeTo = $event->evangelist;
        if (!$routeTo->phone) {
            $routeTo = Models\User
                    ::where('is_admin', '=', true)
                    ->whereNotNull('phone')
                    ->orderByRaw('RAND()')
                    ->first();
        }

        $supportCall->answered_by_username = $routeTo->username;
        $supportCall->save();


        $xml = '<Response>';
        if (strpos($routeTo->phone, '@') !== false) {
            $xml .= '<Sip>'.$routeTo->phone.'</Sip>';
        } else {
            $xml .= '<Dial>'.$routeTo->phone.'</Dial>';
        }
        $xml .= '</Response>';

        $response = \Response::make($xml, 200);
        $response->header('Content-type', 'text/xml');
        return $response;
    }
}
<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

class MessengerHook extends ApiController {
    public function getHook()
    {
        return \Input::get("hub_challenge");
    }

    public function postHook()
    {
        $hash = \Request::header("X-Hub-Signature");

        $request = \Request::instance();
        $content = $request->getContent();

        if(isset($hash)
        && strlen($hash) > 0
        && explode("=", $hash)[1] == hash_hmac("sha1", $content, \Config::get("messenger.app_secret"))) {
            $payload = json_decode($content, true);

            foreach($payload["entry"] as $entry) {
                if($entry["messaging"]) {
                    foreach($entry["messaging"] as $event) {
                        if($event["optin"]) {
                            $registration = Models\Batch\Event\Registration::where("id", "=", $event["optin"]["ref"])->firstOrFail();

                            if($registration->devices->where("service", "messenger")->count() == 0) {
                                $msg_response = Services\FacebookMessenger::SendMessageUserRef("👋 Hey " . $registration->first_name . "! Thanks for registering for CodeDay. I'm here to remind you of important stuff. For example, during the event I will remind you of upcoming workshops, meals, and send you any important announcements from the organizers. Thanks again, and we hope you're super excited for the event! 😁", $event["optin"]["user_ref"]);

                                if(isset($msg_response) && $msg_response != false && $msg_response != null) {
                                    $device = new Models\Batch\Event\Registration\Device;
                                    $device->service = "messenger";
                                    $device->token = $msg_response->recipient_id;
                                    $device->batches_events_registration_id = $registration->id;
                                    $device->save();
                                }
                            }

                            return "ok";
                        }
                    }
                }
            }
        } else {
            return ">:(";
        }
    }
}

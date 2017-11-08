<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;

class NotificationsController extends \CodeDay\Clear\Http\Controller {
    public function getIndex() {
        $event = \Route::input('event');
        $registrations = $event->registrations;

        $app_count = 0;
        $messenger_count = 0;
        $sms_count = 0;
      
        foreach($registrations as $registration) {
            $devices = $registration->devices;

            foreach($devices as $device) {
                switch($device->service) {
                    case "messenger":
                        $messenger_count++;
                        break;
                    case "sms":
                        $sms_count++;
                        break;
                    case "app":
                        $app_count++;
                        break;
                }
            }
        }

        return \View::make('event/notifications', [
            'app_count' => $app_count,
            'messenger_count' => $messenger_count,
            'sms_count' => $sms_count
        ]);
    }

    public function postIndex() {
        $text = trim(\Input::get('text'));
        $event = \Route::input('event');

        $send_app = \Input::get('send_app');
        $send_messenger = \Input::get('send_messenger');
        $send_sms = \Input::get('send_sms');

        if(!isset($text) || strlen($text) == 0) {
            \Session::flash('error', "Notification text is required");
            return \Redirect::to('/event/'.$event->id.'/notifications');
        }

        $registrations = $event->registrations;
        $total_count = 0;
      
        foreach($registrations as $registration) {
            $devices = $registration->devices;

            foreach($devices as $device) {
                switch($device->service) {
                    case "messenger":
                        if($send_messenger == "on") {
                            Services\FacebookMessenger::SendMessage($text, $device->token);
                            $total_count++;
                        }
                        break;
                    case "sms":
                        if($send_sms == "on") {
                            Services\Telephony\Sms::send($device->token, $text);
                            $total_count++;
                        }
                        break;
                    case "app":
                        if($send_app == "on") {
                            Services\Firebase::SendNotification("CodeDay Announcement", $text, $device->token);
                            $total_count++;
                        }
                        break;
                }
            }
        }

        \Session::flash('status_message', 'Notification pushed to ' . $total_count . ' attendees');

        return \Redirect::to('/event/'.$event->id.'/notifications');
    }
}
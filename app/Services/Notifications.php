<?php
namespace CodeDay\Clear\Services;

use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Models\Batch\Event\Registration;

class Notifications {
  public static function SendNotificationsForAnnouncement($announcement) {
    $event = $announcement->event;
    $registrations = $event->registrations;

    foreach($registrations as $registration) {
      $devices = $registration->devices;

      foreach($devices as $device) {
        switch($device->service) {
          case "messenger":
            if($announcement->urgency >= 2) {
              if($announcement->link != null && $announcement->cta != null) {
                FacebookMessenger::SendMessageWithButtons("An announcement from the CodeDay organizers:\n\n" . $announcement->body, $device->token, [
                  [
                    'type' => 'web_url',
                    'url' => $announcement->link,
                    'title' => $announcement->cta
                  ]
                ]);
              } else {
                FacebookMessenger::SendMessage("An announcement from the CodeDay organizers:\n\n" . $announcement->body, $device->token);
              }
            }
            break;
          case "sms":
            if($announcement->urgency == 3) {
              if($announcement->link != null && $announcement->cta != null) {
                Telephony\Sms::send($device->token, "CodeDay Announcement: " . $announcement->body . "\n\n" . $announcement->cta . ": " . $announcement->link);
              } else {
                Telephony\Sms::send($device->token, "CodeDay Announcement: " . $announcement->body);
              }
            }
            break;
          case "app":
            // TODO companion implementation
            break;
        }
      }
    }
  }
}
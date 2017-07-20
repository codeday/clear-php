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
            FacebookMessenger::SendMessage("An announcement from the CodeDay organizers:\n\n" . $announcement->body, $device->token);
            break;
          case "sms":
            Telephony\Sms::send($device->token, "CodeDay Announcement: " . $announcement->body);
            break;
          case "app":
            // TODO companion implementation
            break;
        }
      }
    }

    $job->delete();
  }
}
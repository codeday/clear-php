<?php
namespace CodeDay\Clear\Services;

use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Models\Batch\Event\Registration;

class Notifications {
  public static function SendNotificationsForAnnouncement($announcement) {
    $event = $announcement->event;
    $registrations = $event->registrations;

    // open connections to APNs
    $apns_prod = new \ApnsPHP_Push(\ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, base_path()."/resources/signing/apns_prod.pem");
    $apns_prod->setProviderCertificatePassphrase(\Config::get("apple.apns_prod_password"));

    $apns_dev = new \ApnsPHP_Push(\ApnsPHP_Abstract::ENVIRONMENT_SANDBOX, base_path()."/resources/signing/apns_dev.pem");
    $apns_dev->setProviderCertificatePassphrase(\Config::get("apple.apns_dev_password"));

    $apns_prod->setRootCertificationAuthority(base_path()."/resources/signing/entrust.pem");
    $apns_dev->setRootCertificationAuthority(base_path()."/resources/signing/entrust.pem");

    $apns_prod->connect();
    $apns_dev->connect();

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
            if($announcement->link != null && $announcement->cta != null) {
              Firebase::SendClickableNotification("CodeDay Announcement", $announcement->body, $announcement->link, $device->token);
            } else {
              Firebase::SendNotification("CodeDay Announcement", $announcement->body, $device->token);
            }
            break;
          default:
            if($device->service == "app_ios_dev" || $device->service == "app_ios_prod") {
              $sandbox = $device->service == "app_ios_dev";

              $message = new \ApnsPHP_Message($device->token);
              $message->setText($announcement->body);

              if($sandbox == true) {
                $apns_dev->add($message);
              } else {
                $apns_prod->add($message);
              }
            }
            break;
        }
      }
    }

    \Queue::push(function ($job) use ($apns_prod, $apns_dev) {
      if(!empty($apns_prod->getQueue(false))) {
        $apns_prod->connect();
        $apns_prod->send();
        $apns_prod->disconnect();
        $prod_errors = $apns_prod->getErrors();
        
        if(!empty($prod_errors)) {
          \Log::error($prod_errors);
        }
      }
  
      if(!empty($apns_dev->getQueue(false))) {
        $apns_dev->connect();
        $apns_dev->send();
        $apns_dev->disconnect();
        $dev_errors = $apns_dev->getErrors();
        
        if(!empty($dev_errors)) {
          \Log::error($dev_errors);
        }
      }
      
      $job->delete();
    });
  }

  public static function SendCheckinNotification($registration) {
    $devices = $registration->devices;
    $event = $registration->event;

    foreach($devices as $device) {
      switch($device->service) {
        case "messenger":
          $messageBody = "Welcome to CodeDay " . $event->name . ", " . $registration->first_name . "! Find a place to set yourself up, kickoff will start at noon. Now's also a good time to look over the schedule to see which activities and workshops you'd like to attend.";

          FacebookMessenger::SendMessageWithButtons($messageBody, $device->token, [
            [
              'type' => 'web_url',
              'url' => "https://codeday.org/" . $event->webname,
              'title' => "Event Schedule"
            ]
          ]);
          break;
        case "sms":
          $messageBody = "Welcome to CodeDay " . $event->name . ", " . $registration->first_name . "! Take some time and look over the schedule to see what activities interest you: https://codeday.org/" . $event->webname;

          Telephony\Sms::send($device->token, $messageBody);
          break;
        case "app":
          Firebase::SendClickableNotification("Welcome to CodeDay, " . $registration->first_name . "!", "Take some time and look over the schedule to see what activities interest you.", "https://codeday.org/" . $event->webname, $device->token);
          break;
      }
    }
  }

  public static function SendNotificationsForActivity($activity, $event) {
    $registrations = $event->registrations;
    
    foreach($registrations as $registration) {
      $devices = $registration->devices;

      foreach($devices as $device) {
        switch($device->service) {
          case "messenger":
            $messageBody = "Reminder: " . $activity->title . " is starting in thirty minutes!\n\n" . ($activity->description ? $activity->description : "");

            if(isset($activity->url) && $activity->url != null) {
              FacebookMessenger::SendMessageWithButtons($messageBody, $device->token, [
                [
                  'type' => 'web_url',
                  'url' => $activity->url,
                  'title' => "More Info"
                ]
              ]);
            } else {
              FacebookMessenger::SendMessage($messageBody, $device->token);
            }
            break;
          case "sms":
            $messageBody = "CodeDay Reminder: " . $activity->title . " is starting in thirty minutes!";

            if(isset($activity->url) && $activity->url != null) {
              Telephony\Sms::send($device->token, $messageBody . "\n\nMore Info: " . $activity->url);
            } else {
              Telephony\Sms::send($device->token, $messageBody);
            }
            break;
          case "app":
            if(isset($activity->url) && $activity->url != null) {
              Firebase::SendClickableNotification("Workshop Reminder", $activity->title . " is starting in thirty minutes! Tap for more details.", $activity->url, $device->token);
            } else {
              Firebase::SendNotification("Workshop Reminder", $activity->title . " is starting in thirty minutes!", $device->token);
            }
            break;
        }
      }
    }
  }
}
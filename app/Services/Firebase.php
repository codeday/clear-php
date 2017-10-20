<?php
namespace CodeDay\Clear\Services;

use GuzzleHttp;

/**
 * Supports sending notifications via FCM.
 *
 * @package     CodeDay\Clear\Services
 * @author      TJ Horner <tjhorner@srnd.org>
 * @copyright   (c) 2014-2017 srnd.org
 * @license     Perl Artistic License 2.0
 */
class Firebase {
  public static function Post($endpoint, $payload)
  {
    $url = "https://fcm.googleapis.com/fcm/" . $endpoint;

    $opts = ['http' => ['method'  => 'POST']];

    $opts['http']['header'] = [
      'Content-type: application/json',
      'Authorization: key=' . \Config::get('firebase.api_key')
    ];

    $opts['http']['content'] = json_encode($payload);

    try {
      \Queue::push(function ($job) use ($opts, $url) {
        $context = stream_context_create($opts);
        @file_get_contents($url, false, $context);

        $job->delete();
      });
    } catch (\Exception $ex) {}
  }

  public static function SendNotification($title, $text, $to) {
    $payload = [
      'notification' => [
        'title' => $title,
        'body' => $text,
        'icon' => "https://clear.codeday.org/assets/img/logo-square.png",
        'android_channel_id' => 'event_notifications'
      ],
      'to' => $to
    ];

    self::Post('send', $payload);
  }

  public static function SendClickableNotification($title, $text, $url, $to) {
    $payload = [
      'notification' => [
        'title' => $title,
        'body' => $text,
        'icon' => "https://clear.codeday.org/assets/img/logo-square.png",
        'click_action' => $url,
        'android_channel_id' => 'event_notifications'
      ],
      'to' => $to
    ];

    self::Post('send', $payload);
  }
}
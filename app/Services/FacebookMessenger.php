<?php
namespace CodeDay\Clear\Services;

use GuzzleHttp;

/**
 * Supports sending messages via Facebook Messenger.
 *
 * @package     CodeDay\Clear\Services
 * @author      TJ Horner <tjhorner@srnd.org>
 * @copyright   (c) 2014-2017 srnd.org
 * @license     Perl Artistic License 2.0
 */
class FacebookMessenger {
  protected static $client;

  public static function Post($endpoint, $payload)
  {
    $url = "https://graph.facebook.com/v2.6/" . $endpoint . "?access_token=" . \Config::get('messenger.access_token');
    
    // $ch = \curl_init($url);
    // \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    // curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    // \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    // \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // \curl_exec($ch);
    // \curl_close($ch);

    \Queue::push(function($job) use ($payload, $url)
    {
      $ch = \curl_init($url);
      \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
      \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
      \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      \curl_exec($ch);
      \curl_close($ch);

      $job->delete();
    });
  }

  public static function SendMessage($text, $to, $quick_replies = null) {
    $payload = [
      'recipient' => [
        'id' => $to
      ],
      'message' => [
        'text' => $text
      ]
    ];

    if(isset($quick_replies)) {
      $payload['message']['quick_replies'] = $quick_replies;
    }

    self::Post('me/messages', $payload);
  }

  public static function SendMessageUserRef($text, $to, $quick_replies = null) {
    $payload = [
      'recipient' => [
        'user_ref' => $to
      ],
      'message' => [
        'text' => $text
      ]
    ];

    if(isset($quick_replies)) {
      $payload['message']['quick_replies'] = $quick_replies;
    }

    try{
      $response = self::$client->post("https://graph.facebook.com/v2.6/me/messages", [
        'json' => $payload,
        'query' => [ 'access_token' => \Config::get('messenger.access_token') ]
      ]);
    } catch (\Exception $ex) { return null; }

    if ($response->getStatusCode() == 202) {
      return false;
    } elseif ($response->getStatusCode() != 200) {
      return null;
    } else {
      return json_decode($response->getBody());
    }
  }

  public static function SendMessageWithButtons($text, $to, $buttons) {
    $payload = [
      'recipient' => [
        'id' => $to
      ],
      'message' => [
        'attachment' => [
          'type' => 'template',
          'payload' => [
            'template_type' => 'button',
            'text' => $text,
            'buttons' => $buttons
          ]
        ]
      ]
    ];

    self::Post('me/messages', $payload);
  }

  public static function booting() {
    self::$client = new GuzzleHttp\Client([]);
  }
}

FacebookMessenger::booting();
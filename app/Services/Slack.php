<?php
namespace CodeDay\Clear\Services;

class Slack {
    private static $defaults = [
        'icon_emoji' => ':codeday:',
        'channel' => '#events',
        'username' => 'clear'
    ];

    public static function SendPayload($payload)
    {
        $payload = array_merge(self::$defaults, $payload);
        $url = \Config::get('slack.webhook_url');

        \Queue::push(function($job) use ($payload, $url)
        {
            $ch = \curl_init($url);
            \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_exec($ch);
            \curl_close($ch);

            $job->delete();
        });
    }

    public static function Message($text, $to = null)
    {
        $payload = [
            'text' => $text
        ];
        if (isset($to)) {
            $payload['to'] = $to;
        }

        self::SendPayload($payload);
    }
}
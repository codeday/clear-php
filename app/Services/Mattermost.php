<?php
namespace CodeDay\Clear\Services;

use GuzzleHttp;

/**
 * Supports sending messages to Slack.
 *
 * Contains functionality to send messages to the Slack configured in the local settings.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Mattermost {
    protected static $client;

    /**
     * Sends a payload (asynchronously) to the Mattermost API,
     *
     * @param string    $team       The team to send the webhook to. Mattermost config must have a webhook by this ID.
     * @param array     $payload    Associative array containing the payload data.
     */
    public static function SendPayloadToTeam($team, $payload)
    {
        $url = \Config::get('mattermost.'.$team.'.webhook');
        if ($url) {
            \Queue::push(function($job) use ($payload, $url)
            {
                $ch = \curl_init($url);
                \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                \curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                \curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                \curl_exec($ch);
                \curl_close($ch);

                $job->delete();
            });
        }
    }

    /**
     * Sends a message to a Mattermost room.
     *
     * @param string    $text   Message to send. Markdown and emojicode is supported.
     * @param string    $team   Team to send to.
     * @param string    $room   Room ID to send to.
     */
    public static function Message($team, $room, $text)
    {
        $payload = [
            'channel' => $room,
            'text' => $text,
            'username' => \Config::get('mattermost.'.$team.'.username')
        ];

        self::SendPayloadToTeam($team, $payload);
    }

    public static function booting()
    {
        self::$client = new GuzzleHttp\Client([]);
    }
}

Slack::booting();

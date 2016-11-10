<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use \CodeDay\Clear\Models;
use \Carbon\Carbon;
use \GuzzleHttp;

class IncomingController extends \CodeDay\Clear\Http\Controller {
    public function postIndex()
    {
        if (Carbon::createFromTimestampUTC(\Input::get('timestamp'))->addHours(1)->isPast()
            || hash_hmac('sha256', \Input::get('timestamp').\Input::get('token'), \Config::get('mailgun.key'))
                !== \Input::get('signature')) {
            \App::abort(401);
        }

        $to = \Input::get('recipient');
        $webname = substr($to, 0, strpos($to, '@'));

        $event = Models\Batch\Event
            ::where('batch_id', '=', Models\Batch::Loaded()->id)
            ->where(function($w) use ($webname) {
                return $w
                    ->where('webname_override', '=', $webname)
                    ->orWhere(function($w2) use ($webname) {
                        return $w2
                            ->where('region_id', '=', $webname)
                            ->whereNull('webname_override');
                    });
            })
            ->orderBy('webname_override')
            ->first();

        $forwardTo = (isset($event) && $event->support_destination)
            ? $event->support_destination : 'support@studentrnd.org';

        $messageUrl = \Input::get('message-url');

        \Queue::push(function($job) use ($messageUrl, $forwardTo) {

            echo "Message URL: ".$messageUrl;

            // Get the message from Mailgun
            $client = new GuzzleHttp\Client();
            $result = $client->get($messageUrl, [
                'auth' => ['api', \Config::get('mailgun.key')],
                'headers' => ['Accept' => 'message/rfc2822']
            ]);
            if ($result->getStatusCode() != 200) throw new \Exception($result->getBody());
            $message = json_decode($result->getBody(), true);

            echo "Message:\n\n ".$message['body-mime'];

            // Forward the message
            $client = new GuzzleHttp\Client();
            $client->post('https://mandrillapp.com/api/1.0/messages/send-raw.json', ['body' => json_encode([
                "raw_message" => $message['body-mime'],
                "to" => [
                    $forwardTo
                ],
                "async" => true,
                "key" => \Config::get('mandrill.key')
            ])]);

            $job->delete();
        });
    }
}

<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SlackOauthController extends \CodeDay\Clear\Http\Controller {
  public function getOauth()
  {
    $code = \Input::get('code');
    $event = Models\User::me()->current_managed_events->sortBy('date_created')->last();
    $oauth_data = Services\Slack::GetOauthAccess($code);

    if($oauth_data && $event){
      $webhook = new Models\Application\Webhook;
      $webhook->url = $oauth_data->incoming_webhook->url;
      $webhook->event = "slack.registration.register.".$event->id;
      $webhook->application_id = \Config::get('slack.internal_app');
      $webhook->save();

      Services\Slack::SendPayloadToUrl([
        'text' => ":wave: Hello! If you see this, that means your Clear integration is working. Try registering someone through Clear!"
      ], $oauth_data->incoming_webhook->url);

      \Session::flash('status_message', 'Connected to Slack! We sent a test message, go look for it.');
    }else{
      \Session::flash('error', 'Could not authorize you with Slack. Please try again.');
    }

    \Redirect::to('/event/' . $event->id . '/slack');
  }
}

<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SpotifyOauthController extends \CodeDay\Clear\Http\Controller {
  public function getOauth()
  {
    $error = \Input::get('error');

    if($error) {
      \Session::flash('error', 'We weren\'t able to communicate with Spotify. Try again later.');
      return \Redirect::to('/event/' . $event->id . '/spotify');
    } else {
      $code = \Input::get('code');
      $event = Models\Batch\Event::where("id", "=", \Input::get('state'))->firstOrFail();
  
      if (Models\User::me()->username != $event->manager_username
          && Models\User::me()->username != $event->evangelist_username
          && Models\User::me()->username != $event->coach_username
          && !$event->isUserAllowed(Models\User::me())
          && !Models\User::me()->is_admin) {
          \App::abort(401);
      }

      $access_token = Services\Spotify::ExchangeCode($code);

      if($access_token == false) {
        \Session::flash('error', 'We weren\'t able to communicate with Spotify. Try again later.');
        return \Redirect::to('/event/' . $event->id . '/spotify');
      } else {
         $token_data = json_decode($access_token);
         
         $event->spotify_access_token = $token_data->access_token;
         $event->spotify_refresh_token = $token_data->refresh_token;
         $event->save();

         \Session::flash('status_message', 'Connected to Spotify! Attendees will now see currently playing song during the event.');
         return \Redirect::to('/event/' . $event->id . '/spotify');
      }
    }
  }
  //   $oauth_data = Services\Spotify::GetOauthAccess($code);

  //   if($oauth_data != null && $oauth_data != false && $event && $oauth_data->ok == true){
  //     $webhook = new Models\Application\Webhook;
  //     $webhook->url = $oauth_data->incoming_webhook->url;
  //     $webhook->event = "slack.registration.register.".$event->id;
  //     $webhook->application_id = \Config::get('slack.internal_app');
  //     $webhook->save();

  //     Services\Slack::SendPayloadToUrl([
  //       'text' => ":wave: Hello! If you see this, that means your Clear integration is working. Try registering someone through Clear!"
  //     ], $oauth_data->incoming_webhook->url);

  //     \Session::flash('status_message', 'Connected to Slack! We sent a test message, go look for it.');
  //     return \Redirect::to('/event/' . $event->id . '/slack');
  //   }else{
  //     \Session::flash('error', 'Could not authorize you with Slack. Please try again. (Slack error: '.$oauth_data->error.')');
  //     return \Redirect::to('/');
  //   }
  // }
}

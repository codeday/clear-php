<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Services;

class SpotifyController extends \CodeDay\Clear\Http\Controller {
  public function getIndex()
  {
    $event = \Route::input('event');

    return \View::make('event/spotify', [
      "oauth_uri" => Services\Spotify::GetOauthUri($event->id, [ "user-read-currently-playing", "user-read-playback-state" ]),
      "event" => $event
    ]);
  }

  public function getUnlink()
  {
    $event = \Route::input('event');
    $event->spotify_access_token = null;
    $event->spotify_refresh_token = null;
    $event->save();

    \Session::flash('status_message', 'Unlinked from Spotify.');
    return \Redirect::to('/event/' . $event->id . '/spotify');
  }
}

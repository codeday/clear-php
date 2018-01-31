<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use \CodeDay\Clear\Services;

class Events extends ApiController {
    public function getIndex()
    {
        return json_encode(ModelContracts\Event::Collection(Models\Batch\Event::all(), $this->permissions));
    }

    public function getEvent()
    {
        return json_encode(ModelContracts\Event::Model(\Route::input('event'), $this->permissions));
    }

    public function getVolunteeredFor()
    {
        $this->requirePermission(['internal']);
        $grants = Models\User\Grant::where('username', '=', \Input::get('username'))->get();

        $response = [];

        foreach($grants as $grant){
          array_push($response, ModelContracts\Grant::Model($grant, $this->permissions));
        }

        return json_encode($response);
    }

    public function getHasAccess()
    {
        $user = Models\User::where('username', '=', \Input::get('username'))->firstOrFail();
        if ($user->is_admin) return ModelContracts\Event::Collection(Models\Batch\Event::get(), $this->permissions);

        $events = Models\Batch\Event
            ::select('batches_events.*')
            ->leftJoin('users_grants', 'users_grants.batches_event_id', '=', 'batches_events.id')
            ->where('manager_username', '=', $user->username)
            ->orWhere('coach_username', '=', $user->username)
            ->orWhere('evangelist_username', '=', $user->username)
            ->orWhere('users_grants.username', '=', $user->username)
            ->get();

        return ModelContracts\Event::Collection($events, $this->permissions);
    }

    public function getNowPlaying() {
        $event = \Route::input('event');
        if($event->spotify_access_token) {
            $now_playing = Services\Spotify::GetUserNowPlaying($event->spotify_access_token);

            if($now_playing == false) {
                $new_access_token = Services\Spotify::RefreshAccessToken($event->spotify_refresh_token);

                if($new_access_token == false) {
                    return json_encode([
                        "spotify_linked" => false,
                        "now_playing" => null,
                        "error" => "Internal error: Could not refresh Spotify access token"
                    ]);
                } else {
                    $access_token_res = json_decode($new_access_token);
                    $event->spotify_access_token = $access_token_res->access_token;
                    $event->save();

                    $now_playing = Services\Spotify::GetUserNowPlaying($event->spotify_access_token);
                }
            }

            $player_info = json_decode($now_playing);

            if(!isset($player_info->error) && isset($player_info->is_playing)) {
                return json_encode([
                    "spotify_linked" => true,
                    "now_playing" => $player_info->is_playing ? [
                        "track" => $player_info->item->name,
                        "artist" => $player_info->item->artists[0]->name,
                        "album" => [
                            "name" => $player_info->item->album->name,
                            "image" => $player_info->item->album->images[0]->url
                        ],
                        "link" => $player_info->item->external_urls->spotify
                    ] : null
                ]);
            } else {
                return json_encode([
                    "spotify_linked" => true,
                    "now_playing" => null
                ]);
            }
        } else {
            return json_encode([
                "spotify_linked" => false,
                "now_playing" => null
            ]);
        }
    }

    public function getRegistrations()
    {
      $this->requirePermission(['admin']);
      $event = \Route::input('event');
      return json_encode(ModelContracts\Registration::Collection($event->registrationsSortedBy("first_name", "asc"), $this->permissions));
    }

    public function postRegistrations()
    {
      $this->requirePermission(['admin']);
      $event = \Route::input('event');

      $registration = Services\Registration::CreateRegistrationRecord(
          $event,
          \Input::get('first_name'), \Input::get('last_name'),
          \Input::get('email'), "student");

      if ($registration->type !== 'student') {
          $registration->parent_no_info = true;
      }
      $registration->save();

      return json_encode(ModelContracts\Registration::Model($registration, $this->permissions));
    }

    public function getAnnouncements()
    {
        $event = \Route::input('event');
        return json_encode(ModelContracts\Announcement::Collection($event->announcements, $this->permissions));
    }

    public function getManagedBy()
    {
        $this->requirePermission(['internal']);
        $user = Models\User::where('username', '=', \Route::input('username'))->get();
        return json_encode($user);
    }
}

<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;

class AnnouncementsController extends \CodeDay\Clear\Http\Controller {
    public function getIndex() {
      return \View::make('event/announcements');
    }

    public function postDelete()
    {
        if (Models\User::me()->is_admin) {
          $event = \Route::input('event');
          $event->announcements()->delete();

          \Session::flash('status_message', 'Announcements deleted');

          return \Redirect::to('/event/'.$event->id.'/announcements');
        }else{
          \App::abort(403);
        }
    }

    public function postIndex() {
      $text = trim(\Input::get('text'));
      $urgency = (int) \Input::get('urgency');
      $event = \Route::input('event');

      $cta = trim(\Input::get('cta'));
      $link = trim(\Input::get('link'));

      if(!isset($text) || strlen($text) == 0) {
        \Session::flash('error', "Announcement text is required");
        return \Redirect::to('/event/'.$event->id.'/announcements');
      }

      if(strlen($cta) > 0 && strlen($link) == 0 || strlen($link) > 0 && strlen($cta) == 0) {
        \Session::flash('error', "You must set both link URL and link text if including a link");
        return \Redirect::to('/event/'.$event->id.'/announcements');
      }
      
      $announcement = new Models\Batch\Event\Announcement;
      $announcement->batches_event_id = $event->id;
      $announcement->creator_username = Models\User::me()->username;
      $announcement->body = $text;
      $announcement->urgency = $urgency;

      if(strlen($cta) > 0 && strlen($link) > 0) {
        $announcement->cta = $cta;
        $announcement->link = $link;
      }

      $announcement->save();

      Services\Notifications::SendNotificationsForAnnouncement($announcement);

      \Session::flash('status_message', 'Announcement posted');

      return \Redirect::to('/event/'.$event->id.'/announcements');
    }
}
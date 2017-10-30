<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \CodeDay\Clear\ModelContracts;

class NotificationsController extends \CodeDay\Clear\Http\Controller {
    public function getIndex() {
      return \View::make('event/notifications');
    }

    public function postIndex() {
      $text = trim(\Input::get('text'));
      $event = \Route::input('event');

      $send_app = \Route::input('send_app');
      $send_messenger = \Route::input('send_messenger');
      $send_sms = \Route::input('send_sms');

      $cta = trim(\Input::get('cta'));
      $link = trim(\Input::get('link'));

      if(!isset($text) || strlen($text) == 0) {
        \Session::flash('error', "Notification text is required");
        return \Redirect::to('/event/'.$event->id.'/notifications');
      }

      if(strlen($cta) > 0 && strlen($link) == 0 || strlen($link) > 0 && strlen($cta) == 0) {
        \Session::flash('error', "You must set both link URL and link text if including a link");
        return \Redirect::to('/event/'.$event->id.'/notifications');
      }

      

      \Session::flash('status_message', 'Notifications pushed');

      return \Redirect::to('/event/'.$event->id.'/notifications');
    }
}
<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SubscriptionsController extends \Controller {
  public function getIndex()
  {
    return \View::make('event/subscriptions');
  }

  public function postDelete()
  {
    $event = \Route::input('event');
    $subscription = Models\Batch\Event\Notify::where('id', '=', \Input::get('id'))->firstOrFail();

    if ($subscription->batches_event_id !== $event->id) {
        \App::abort(401);
    }

    \Session::flash('status_message', 'Subscription removed');
    $subscription->delete();
    return \Redirect::to('/event/'.$event->id.'/subscriptions');
  }
}

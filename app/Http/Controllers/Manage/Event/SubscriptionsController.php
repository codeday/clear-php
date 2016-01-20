<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SubscriptionsController extends \CodeDay\Clear\Http\Controller {
  public function getIndex()
  {
    return \View::make('event/subscriptions');
  }

  public function postDelete()
  {
    $event = \Route::input('event');
    $subscription = Models\Notify::where('id', '=', \Input::get('id'))->firstOrFail();

    if ($subscription->batches_event_id !== $event->id) {
        \App::abort(401);
    }

    \Session::flash('status_message', 'Subscription removed');
    $subscription->delete();
    return \Redirect::to('/event/'.$event->id.'/subscriptions');
  }
}

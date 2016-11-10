<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class ActivitiesController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/activities/index');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $activity = new Models\Batch\Event\Activity;
        $activity->batches_event_id = $event->id;
        $activity->title = \Input::get('title');
        $activity->type = \Input::get('type');
        $activity->time = floatval(\Input::get('time'));
        $activity->url = \Input::get('url') ? \Input::get('url') : null;
        $activity->description = \Input::get('description') ? \Input::get('description') : null;
        $activity->save();

        \Session::flash('status_message', 'Activity added');

        return \Redirect::to('/event/'.$event->id.'/activities');
    }

    public function postDelete()
    {
        $event = \Route::input('event');

        $activity = Models\Batch\Event\Activity::find(\Input::get('id'));
        if (!$activity || $activity->batches_event_id != $event->id) {
            \App::abort(404);
        }

        \Session::flash('status_message', 'Activity removed');

        $activity->delete();

        return \Redirect::to('/event/'.\Route::input('event')->id.'/activities');
    }
} 
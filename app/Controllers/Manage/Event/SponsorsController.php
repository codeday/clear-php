<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SponsorsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/sponsors/index');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $sponsor = new Models\Batch\Event\Sponsor;
        $sponsor->batches_event_id = $event->id;
        $sponsor->name = \Input::get('name');
        $sponsor->url = \Input::get('url');
        $sponsor->logo = file_get_contents(\Input::file('logo'));
        $sponsor->description = \Input::get('description');
        $sponsor->save();

        return \Redirect::to('/event/'.$event->id.'/sponsors');
    }

    public function postDelete()
    {
        $event = \Route::input('event');

        $sponsor = Models\Batch\Event\Sponsor::find(\Input::get('id'));
        if (!$sponsor || $sponsor->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $sponsor->delete();

        return \Redirect::to('/event/'.\Route::input('event')->id.'/sponsors');
    }
} 
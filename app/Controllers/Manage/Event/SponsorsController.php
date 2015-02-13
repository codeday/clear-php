<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SponsorsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/sponsors/index');
    }

    public function getAdd()
    {
        return \View::make('event/sponsors/add_edit');
    }

    public function postAdd()
    {
        $event = \Route::input('event');

        $sponsor = new Models\Batch\Event\Sponsor;
        $sponsor->batches_event_id = $event->id;
        $sponsor->name = \Input::get('name');
        $sponsor->url = \Input::get('url');
        $sponsor->logo = file_get_contents(\Input::file('logo'));
        $sponsor->blurb = \Input::get('blurb');
        $sponsor->description = \Input::get('description');
        $sponsor->perk = \Input::get('perk');
        $sponsor->save();

        \Session::flash('status_message', 'Sponsor added');

        return \Redirect::to('/event/'.$event->id.'/sponsors');
    }

    public function getEdit()
    {
        $event = \Route::input('event');
        $sponsor = \Route::input('sponsor');

        if ($sponsor->batches_event_id != $event->id) {
            \App::abort(404);
        }

        return \View::make('event/sponsors/add_edit', ['sponsor' => $sponsor]);
    }

    public function postEdit()
    {
        $event = \Route::input('event');
        $sponsor = \Route::input('sponsor');

        if ($sponsor->batches_event_id != $event->id) {
            \App::abort(404);
        }

        $sponsor->name = \Input::get('name');
        $sponsor->url = \Input::get('url');

        if (\Input::hasFile('logo')) {
            $sponsor->logo = file_get_contents(\Input::file('logo'));
        }

        $sponsor->blurb = \Input::get('blurb');
        $sponsor->description = \Input::get('description');
        $sponsor->perk = \Input::get('perk');
        $sponsor->save();

        \Session::flash('status_message', 'Sponsor saved');

        return \Redirect::to('/event/'.$event->id.'/sponsors/'.$sponsor->id.'/edit');
    }

    public function postDelete()
    {
        $event = \Route::input('event');
        $sponsor = \Route::input('sponsor');

        if (!$sponsor || $sponsor->batches_event_id != $event->id) {
            \App::abort(404);
        }

        \Session::flash('status_message', 'Sponsor removed');

        $sponsor->delete();

        return \Redirect::to('/event/'.\Route::input('event')->id.'/sponsors');
    }
} 
<?php
namespace CodeDay\Clear\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;

class DeckController extends \Controller {

    public function getIndex()
    {
        return \View::make('dayof/deck/index');
    }

    public function getSlides()
    {
        return \View::make('dayof/deck/slides', ['event' => $this->getEvent()]);
    }

    public function getNotes()
    {
        return \View::make('dayof/deck/notes', ['event' => $this->getEvent()]);
    }

    private function getEvent()
    {
        $event_id = \Input::get('event');
        return Models\Batch\Event::where('id', '=', $event_id)->firstOrFail();
    }

    private function checkAccess()
    {
        $event = $this->getEvent();

        if (Models\User::me()->username != $event->manager_username
            && Models\User::me()->username != $event->evangelist_username
            && !$event->isUserAllowed(Models\User::me())
            && !Models\User::me()->is_admin) {
            \App::abort(401);
        }
    }
}
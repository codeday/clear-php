<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class NotesController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        $event = \Route::input('event');
        $regNotes = Models\Batch\Event\Registration::where('batches_event_id', '=', $event->id)
                                ->whereNotNull('notes')
                                ->get();
        return \View::make('event/notes', ['regNotes' => $regNotes]);
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $event->notes = \Input::get('notes');
        $event->public_notes = \Input::get('public_notes');
        $event->save();

        return \Redirect::to('/event/'.$event->id.'/notes');
    }

}

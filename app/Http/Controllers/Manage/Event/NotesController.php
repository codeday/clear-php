<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use CodeDay\Clear\Models;
use CodeDay\Clear\ModelContracts;
use CodeDay\Clear\Services;

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

        $sendUpdate = false;
        if ($event->notes !== \Input::get('notes')) $sendUpdate = true;

        $event->notes = \Input::get('notes');
        $event->public_notes = \Input::get('public_notes');
        $event->save();

        if ($sendUpdate) {
            $who = Models\User::me();
            $notes = $event->notes;

            $users = [$event->manager, $event->evangelist];
            foreach ($event->grants as $grant) $users[] = $grant->user;

            foreach ($users as $user) {
                if ($user === null) continue;
                Services\Email::SendOnQueue(
                    'CodeDay', 'codeday@srnd.org',
                    $user->name, $user->email,
                    'Show Notes: '.$event->full_name, null,
                    \View::make('emails/actions/shownotes', ['who' => $who, 'event' => $event, 'notes' => $notes]),
                    false
                );
            }
        }

        return \Redirect::to('/event/'.$event->id.'/notes');
    }

}

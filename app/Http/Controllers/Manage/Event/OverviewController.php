<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class OverviewController extends \CodeDay\Clear\Http\Controller {
  public function getIndex()
  {
    $event = \Route::input('event');
    $regNotes = Models\Batch\Event\Registration::where('batches_event_id', '=', $event->id)
                            ->whereNotNull('notes')
                            ->get();
    return \View::make('event/overview', ['regNotes' => $regNotes]);
  }

  public function getRaw()
  {
    $event = \Route::input('event');
    $regNotes = Models\Batch\Event\Registration::where('batches_event_id', '=', $event->id)
                            ->whereNotNull('notes')
                            ->get();
    return \View::make('event/overview-raw', ['regNotes' => $regNotes]);
  }
}

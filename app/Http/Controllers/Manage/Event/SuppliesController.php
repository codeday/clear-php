<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SuppliesController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/supplies');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $supply = new Models\Batch\Supply;
        $supply->batches_event_id = $event->id;
        $supply->item = \Input::get('item');
        $supply->type = \Input::get('type');
        $supply->quantity = floatval(\Input::get('quantity'));
        $supply->save();

        \Session::flash('status_message', 'Supply added');

        return \Redirect::to('/event/'.$event->id.'/supplies');
    }

    public function postDelete()
    {
        $event = \Route::input('event');

        $supply = Models\Batch\Supply::find(\Input::get('id'));
        if (!$supply || $supply->batches_event_id != $event->id) {
            \App::abort(404);
        }

        \Session::flash('status_message', 'Supply removed');

        $supply->delete();

        return \Redirect::to('/event/'.\Route::input('event')->id.'/supplies');
    }
} 
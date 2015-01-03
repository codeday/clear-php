<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class PromotionsController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/promotions');
    }

    public function postNew()
    {
        $event = \Route::input('event');

        $code = \Input::get('code');
        if (!$code) {
            $code = str_random(15);
        }

        $expires_at = \Input::get('expires_at') ? \Input::get('expires_at') : null;
        $allowed_uses = \Input::get('allowed_uses') ? \Input::get('allowed_uses') : null;

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper($code);
        $promotion->notes = \Input::get('notes');
        $promotion->percent_discount = \Input::get('percent_discount');
        $promotion->expires_at = $expires_at;
        $promotion->allowed_uses = $allowed_uses;
        $promotion->save();

        \Session::flash('status_message', 'Promotion '.$code.' added');

        return \Redirect::to('/event/'.$event->id.'/promotions');
    }

    public function postDelete()
    {
        $event = \Route::input('event');
        $code = Models\Batch\Event\Promotion::where('id', '=', \Input::get('id'))->firstOrFail();

        if ($code->batches_event_id !== $event->id) {
            \App::abort(401);
        }

        if (count($code->registrations) > 0) {
            \Session::flash('error', 'Cannot remove a promotion with existing registrations');
            return \Redirect::to('/event/'.$event->id.'/promotions');
        }

        \Session::flash('status_message', 'Promotion removed');
        $code->delete();
        return \Redirect::to('/event/'.$event->id.'/promotions');
    }
} 
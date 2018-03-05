<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class SpecialController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/special', ['all_batches' => Models\Batch::get()]);
    }

    public function postIndex()
    {
        $event = \Route::input('event');
        $event->custom_css = \Input::get('custom_css') ? \Input::get('custom_css') : null;
        $event->hide_default_workshops = \Input::get('hide_default_workshops') ? true : false;
        $event->hide_meals = \Input::get('hide_meals') ? true : false;
        $event->notice = \Input::get('notice') ? \Input::get('notice') : null;
        $event->name_override = \Input::get('name_override') ? \Input::get('name_override') : null;
        $event->abbr_override = \Input::get('abbr_override') ? \Input::get('abbr_override') : null;
        $event->waiver_id = \Input::get('waiver_id') ? \Input::get('waiver_id') : null;
        $event->webname_override = \Input::get('webname_override') ? \Input::get('webname_override') : null;
        $event->price_earlybird = \Input::get('price_earlybird') ?? 10;
        $event->price_regular = \Input::get('price_regular') ?? 20;
        $event->batch_id = \Input::get('batch_id');
        $event->save();

        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postAddlink()
    {
        $event = \Route::input('event');
        $link = new Models\Batch\Event\SpecialLink;
        $link->name = \Input::get('name');
        $link->url = \Input::get('url');
        $link->new_window = \Input::get('new_window') ? true : false;
        $link->location = 'header';
        $link->batches_event_id = $event->id;
        $link->save();

        \Session::flash('success_message', 'Link added');
        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postDeletelink()
    {
        $event = \Route::input('event');
        $link = Models\Batch\Event\SpecialLink::find(\Input::get('id'));
        if ($link->batches_event_id !== $event->id) \App::abort(404);

        $link->delete();

        \Session::flash('success_message', 'Link removed');
        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postClearwaivers()
    {
        $event = \Route::input('event');

        foreach ($event->registrations as $registration) {
            $registration->waiver_signing_id = null;
            $registration->waiver_signing_link = null;
            $registration->waiver_pdf_link = null;
            $registration->save();
        }

        \Session::flash('success_message', 'Waivers cleared');
        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postCancel()
    {
        $event = \Route::input('event');
        foreach ($event->registrations as $reg) {
            Services\Registration::SendCancelEmail($reg, true);
            Services\Registration::CancelRegistration($reg, true, false);
        }

        \Session::flash('success_message', 'Tickets Cancelled');
        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postDelete()
    {
        $event = \Route::input('event');
        $event->delete();

        \Session::flash('success_message', 'Event removed');
        return \Redirect::to('/');
    }

    public function postOverflow()
    {
        $currentEvent = \Route::input('event');
        $event = new Models\Batch\Event;
        $event->region_id = $currentEvent->region_id;
        $event->batch_id = $currentEvent->batch_id;
        $event->registration_estimate = 100;
        $event->name_override = $currentEvent->name.' 2';
        $event->webname_override = $currentEvent->webname.'-2';
        $event->abbr_override = substr($currentEvent->abbr, 0, 2).'*';
        $event->overflow_for_id = $currentEvent->id;
        $event->save();

        return \Redirect::to('/event/'.$event->id);
    }
}

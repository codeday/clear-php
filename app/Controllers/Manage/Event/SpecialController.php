<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SpecialController extends \Controller {
    public function getIndex()
    {
        return \View::make('event/special');
    }

    public function postIndex()
    {
        $event = \Route::input('event');
        $event->custom_css = \Input::get('custom_css') ? \Input::get('custom_css') : null;
        $event->hide_default_workshops = \Input::get('hide_default_workshops') ? true : false;
        $event->notice = \Input::get('notice') ? \Input::get('notice') : null;
        $event->name_override = \Input::get('name_override') ? \Input::get('name_override') : null;
        $event->abbr_override = \Input::get('abbr_override') ? \Input::get('abbr_override') : null;
        $event->webname_override = \Input::get('webname_override') ? \Input::get('webname_override') : null;
        $event->save();

        return \Redirect::to('/event/'.$event->id.'/special');
    }

    public function postOverflow()
    {
        $currentEvent = \Route::input('event');
        $event = new Models\Batch\Event;
        $event->region_id = $currentEvent->region_id;
        $event->batch_id = $currentEvent->batch_id;
        $event->name_override = $currentEvent->name.' Overflow';
        $event->webname_override = $currentEvent->webname.'-overflow';
        $event->abbr_override = substr($currentEvent->abbr, 0, 2).'*';
        $event->overflow_for_id = $currentEvent->id;
        $event->save();

        return \Redirect::to('/event/'.$event->id);
    }
} 
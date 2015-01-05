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
        $event->save();

        return \Redirect::to('/event/'.$event->id.'/special');
    }
} 
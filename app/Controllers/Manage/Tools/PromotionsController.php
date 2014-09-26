<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class PromotionsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/promotions');
    }

    public function postNew()
    {
        foreach (Models\Batch::loaded()->events as $event) {
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
        }

        return \Redirect::to('/tools/promotions');
    }


} 
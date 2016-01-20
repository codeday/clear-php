<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class GiftCardsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('tools/giftcards');
    }

    public function postIndex()
    {
        $codes = explode("\n", \Input::get('codes'));
        $created = 0;
        foreach ($codes as $code) {
            $code = trim($code);
            if (!Models\GiftCard::where('code', '=', $code)->exists()) {
                $card = new Models\GiftCard;
                $card->code = $code;
                $card->save();
                $created++;
            }
        }

        $notCreated = count($codes) - $created;
        $notCreatedMessage = '';
        if ($notCreated > 0) {
            $notCreatedMessage .= ' '.$notCreated.' already existed and were not created.';
        }

        \Session::flash('status_message', $created.' codes created.'.$notCreatedMessage);
        return \Redirect::to('/tools/giftcards');
    }
}
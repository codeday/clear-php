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
        if (\Input::has('count') && \Input::get('count') > 0) {
            $codes = [];
            for ($i = 0; $i < \Input::get('count'); $i++) {
                $codes[] = self::generateUnambiguousRandomString($length = 10);
            }
        }
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

        if (\Input::has('count') && \Input::get('count') > 0) {
            return implode('<br />', $codes);
        } else {
            return \Redirect::to('/tools/giftcards');
        }
    }

    private static function generateUnambiguousRandomString($length = 8) {
        $characters = '234679ABCDEFGHKMNPQRWXY';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

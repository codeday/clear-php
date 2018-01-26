<?php
namespace CodeDay\Clear\Http\Controllers\Manage\DayOf;

use \CodeDay\Clear\Models;

class CodeCupController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $keys = [];
        foreach (Models\Batch::Managed()->events as $event) {
            $keyFull = hash_hmac('whirlpool', $event->webname, config('app.key'));
            $keys[$event->webname] = substr($keyFull, 0, 3).'-'.substr($keyFull, 3, 3);
        }
        return \View::make('dayof/codecup', ['keys' => $keys]);
    }
}

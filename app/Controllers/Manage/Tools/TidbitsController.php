<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class TidbitsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/tidbits/index');
    }

    public function getEvent()
    {
        return \View::make('tools/tidbits/event');
    }

}

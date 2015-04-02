<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class TidbitsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/tidbits/index');
    }

    public function getRegion()
    {
        return \View::make('tools/tidbits/region', ['region' => \Route::input('region')]);
    }

}

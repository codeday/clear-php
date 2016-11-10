<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class TidbitsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('tools/tidbits/index');
    }

    public function getRegion()
    {
        return \View::make('tools/tidbits/region', ['region' => \Route::input('region')]);
    }

}

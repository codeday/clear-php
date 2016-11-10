<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class PreviewController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('tools/preview');
    }

    public function postIndex()
    {
        return \View::make(\Input::get('view'), [
            'registration' => Models\Batch\Event\Registration::where('id', '=', \Input::get('id'))->firstOrFail(),
            'user' => Models\User::me()
        ]);
    }
}

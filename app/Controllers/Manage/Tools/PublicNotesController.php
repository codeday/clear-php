<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class PublicNotesController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/public_notes/index');
    }

    public function getEvent()
    {
        return \View::make('tools/public_notes/event');
    }

}

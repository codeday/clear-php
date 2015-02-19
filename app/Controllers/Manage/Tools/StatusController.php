<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \Codeday\Clear\Models;

class StatusController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/status');
    }
} 
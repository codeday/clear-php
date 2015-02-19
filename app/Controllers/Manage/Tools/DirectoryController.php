<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \Codeday\Clear\Models;

class DirectoryController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/directory');
    }
} 
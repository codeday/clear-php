<?php
namespace CodeDay\Clear\Controllers\Manage\Settings;

class IndexController extends \Controller {

    public function getIndex()
    {
        return \Redirect::to('/settings/batches');
    }
} 
<?php
namespace CodeDay\Clear\Controllers\Manage\Settings;

class RegionsController extends \Controller {

    public function getIndex()
    {
        return \View::make('settings/regions/index');
    }
} 
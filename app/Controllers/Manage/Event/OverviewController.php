<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class OverviewController extends \Controller {
  public function getIndex()
  {
    return \View::make('event/overview');
  }

  public function getRaw()
  {
    return \View::make('event/overview-raw');
  }
}

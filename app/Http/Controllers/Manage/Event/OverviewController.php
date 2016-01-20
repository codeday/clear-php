<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class OverviewController extends \CodeDay\Clear\Http\Controller {
  public function getIndex()
  {
    return \View::make('event/overview');
  }

  public function getRaw()
  {
    return \View::make('event/overview-raw');
  }
}

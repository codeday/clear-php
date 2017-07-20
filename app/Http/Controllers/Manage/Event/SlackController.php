<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

class SlackController extends \CodeDay\Clear\Http\Controller {
  public function getIndex()
  {
    return \View::make('event/slack');
  }
}

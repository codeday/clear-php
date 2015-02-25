<?php
namespace CodeDay\Clear\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class SubscriptionsController extends \Controller {
  public function getIndex()
  {
    return \View::make('event/subscriptions');
  }
}

<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;

class Promotions extends ApiController {
  // TODO fix these APIs for public use

  public function getPromotion()
  {
    $this->requirePermission(['admin']);
    $promotion = Models\Batch\Event\Promotion::where('id', '=', \Route::input('promotion'))->firstOrFail();

    // TODO Create model contract for promotions.
    // This should work for now since we are assuming
    // that the app has admin permissions.
    $json = $promotion;
    $json["uses"] = $promotion->registrations->count();
    return json_encode($json);
  }

  public function postNew()
  {
    $this->requirePermission(['admin']);

    $promotion = new Models\Batch\Event\Promotion;
    $promotion->batches_event_id = \Input::get('event');
    $promotion->code = strtoupper(\Input::get('promo'));
    $promotion->notes = \Input::get('notes');
    $promotion->percent_discount = "20";
    $promotion->expires_at = null;
    $promotion->allowed_uses = null;
    $promotion->created_by_user = \Input::get("username");
    $promotion->save();

    // TODO Create model contract for promotions.
    // This should work for now since we are assuming
    // that the app has admin permissions.
    return json_encode($promotion);
  }

  public function postDelete()
  {
    $this->requirePermission(['admin']);
    $promotion = Models\Batch\Event\Promotion::where('id', '=', \Input::get('id'))->firstOrFail();
    $promotion->delete();
    return json_encode(["success" => true]);
  }
}

<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Promotions extends ApiController {
  // TODO fix these APIs for public use

  public function getPromotion()
  {
    $application = Models\Application::where('public', '=', \Input::get('token'))->firstOrFail();
    if($application->private == \Input::get('secret') && $application->permission_admin){
      $promotion = Models\Batch\Event\Promotion::where('id', '=', \Route::input('promotion'))->firstOrFail();

      // TODO Create model contract for promotions.
      // This should work for now since we are assuming
      // that the app has admin permissions.
      $json = $promotion;
      $json["uses"] = $promotion->registrations->count();
      return json_encode($json);
    }else{
      \App::abort(401, "Bad secret or no admin permissions");
    }
  }

  public function postNew()
  {
    $application = Models\Application::where('public', '=', \Input::get('token'))->firstOrFail();
    if($application->private == \Input::get('secret') && $application->permission_admin){
      $promotion = new Models\Batch\Event\Promotion;
      $promotion->batches_event_id = \Input::get('event');
      $promotion->code = strtoupper(\Input::get('code'));
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
    }else{
      \App::abort(401, "Bad secret or no admin permissions");
    }
  }

  public function postDelete()
  {
    $application = Models\Application::where('public', '=', \Input::get('token'))->firstOrFail();
    if($application->private == \Input::get('secret') && $application->permission_admin){
      $promotion = Models\Batch\Event\Promotion::where('id', '=', \Input::get('id'))->firstOrFail();
      $promotion->delete();
      return json_encode(["success" => true]);
    }else{
      \App::abort(401, "Bad secret or no admin permissions");
    }
  }
}

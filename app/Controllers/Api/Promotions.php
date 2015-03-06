<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Promotions extends ApiController {
  public function postNew()
  {
    $application = Models\Application::where('public', '=', \Input::get('token'))->first();
    if($application->private == \Input::get('secret') && $application->permission_admin){
      return "ayy";
    }else{
      \App::abort(401, "Bad secret or no admin permissions");
    }
  }
}

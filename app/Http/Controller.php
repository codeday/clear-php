<?php

namespace CodeDay\Clear\Http;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(){
      if(Models\User::me() !== null){
        $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');

        $token_json = strtr(rawurlencode(json_encode((object)[
          'eventId' => getDayOfEvent()->id,
          'eventName' => getDayOfEvent()->fullName,
          'token' => Models\User::me()->token
        ])), $revert);
      }
    }
}

<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class TokenController extends ApiController {
    public function getToken()
    {
    	$user = Models\User::fromToken(\Route::input('token'));
        
        if(isset($user)){
        	return json_encode(ModelContracts\User::Model($user, ["internal"]));
       	}else{
       		\App::abort(403);
       	}
    }
}
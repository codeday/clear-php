<?php
namespace CodeDay\Clear\Http\Controllers\Apiv2;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class OAuthController extends Apiv2Controller {
    public function getExchangeCode()
    {
        $token = Models\User\OAuthToken::where('token', '=', \Input::get('code'))->first();
        
        if($token == null) {
            return $this->apiError("Invalid code parameter");
        }

        if($token->application_token != $this->application->public) {
            return $this->apiError("Code does not belong to application");
        }

        if($token->access_token_used) {
            return $this->apiError("Code has already been used");
        }

        $token->access_token_used = true;
        $token->save();

        return $this->apiSuccess([
            "access_token" => $token->access_token,
            "scopes" => $token->scope_array,
            "user" => ModelContracts\User::Model($token->user, $this->permissions)
        ]);
    }
}

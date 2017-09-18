<?php
namespace CodeDay\Clear\Http\Controllers;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class OAuthController extends \CodeDay\Clear\Http\Controller {
    public function getConsent()
    {
        // Find application or fail
        $application = Models\Application::where('public', '=', \Input::get("client_id"))->firstOrFail();
        $scopes = Models\User\OAuthToken::humanReadableScopes(explode(" ", \Input::get("scope")));
        $rawScopes = Models\User\OAuthToken::validScopes(explode(" ", \Input::get("scope")));
        $redirectUri = \Input::get("redirect_uri");

        return \View::make("oauth/consent", [
            "application" => $application,
            "scopes" => $scopes,
            "rawScopes" => join(" ", $rawScopes),
            "redirect_uri" => $redirectUri
        ]);
    }

    public function postConsent()
    {
        $application = Models\Application::where('public', '=', \Input::get("application"))->firstOrFail();
        $scopes = Models\User\OAuthToken::validScopes(explode(" ", \Input::get("scope")));
        $redirectUri = \Input::get("redirect_uri");

        $token = new Models\User\OAuthToken;
        $token->application_token = $application->public;
        $token->user_username = Models\User::me()->username;
        $token->scopes = join(" ", $scopes);
        $token->save();

        return redirect($redirectUri . "?code=" . $token->token);
    }
}

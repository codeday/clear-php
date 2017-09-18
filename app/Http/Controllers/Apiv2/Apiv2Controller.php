<?php
namespace CodeDay\Clear\Http\Controllers\Apiv2;

use \CodeDay\Clear\Models;

abstract class Apiv2Controller extends \CodeDay\Clear\Http\Controller {
    protected $permissions = [ ];
    protected $application = null;
    protected $user = null;
    protected $scopes = null;

    public function __construct()
    {
        if (!isset($this->requiresApplication) || $this->requiresApplication) {
            $this->setApplicationOrFail();
        }

        if (null !== \Request::header("Authorization")) {
            $this->setUserOrFail();
        }
    }

    protected function apiError($message = "Internal error")
    {
        header("Content-Type: application/json");
        return json_encode([
            "ok" => false,
            "error" => $message
        ]);
    }

    protected function apiSuccess($result)
    {
        header("Content-Type: application/json");
        return json_encode([
            "ok" => true,
            "result" => $result
        ]);
    }

    protected function setApplicationOrFail()
    {
        if (!$this->application) {
            $token = \Input::get('token');
            $secret = \Input::get('secret');

            $application = Models\Application::where('public', '=', $token)->first();

            if (!$application || $application->private !== $secret) {
                die($this->apiError("Unauthorized"));
            } else {
                if ($application->permission_admin) {
                    $this->permissions[] = 'admin';
                }

                if ($application->permission_internal) {
                    $this->permissions[] = 'internal';
                }

                $this->application = $application;
            }
        }
    }

    protected function setUserOrFail()
    {
        if(!isset($this->user)) {
            $token = str_replace("Bearer ", "", \Request::header("Authorization"));
            $oauthToken = Models\User\OAuthToken::where('access_token', '=', $token)->firstOrFail();
            $this->user = Models\User::fromOAuthToken($token);
            $this->scopes = $oauthToken->scope_array;

            if($this->user == null) {
                die($this->apiError("Unauthorized"));
            }

            if($oauthToken->application_token != $this->application->public) {
                die($this->apiError("Unauthorized"));
            }
        }
    }

    protected function requireScopes($scopes = [])
    {
        $this->setUserOrFail();
        foreach ($scopes as $scope) {
            if (!in_array(trim($scope), $this->scopes)) {
                die($this->apiError("Missing at least one required scope: " . join(" ", $scopes)));
            }
        }
        return true;
    }

    protected function requirePermission($or = [])
    {
        $this->setApplicationOrFail();
        foreach ($or as $permission) {
            if (in_array(trim($permission), $this->permissions)) {
                return true;
            }
        }
        die($this->apiError("Missing required permission: " .  join(" ", $or)));
    }
}

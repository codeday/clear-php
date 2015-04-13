<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

abstract class ApiController extends \Controller {
    protected $permissions = [];
    protected $application = null;

    public function __construct()
    {
        if (!isset($this->requiresApplication) || $this->requiresApplication) {
            $this->setApplicationOrFail();
        }

        \App::after(function($request, $response)
        {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', '*');
            $response->headers->set('Content-type', 'text/javascript');
        });
    }

    protected function setApplicationOrFail()
    {
        if (!$this->application) {
            // TODO: deprecate all other forms of "public" and "private"
            $public = \Input::get('public') ? \Input::get('public') : \Input::get('token');
            if (!$public) {
                $public = \Input::get('access_token');
            }

            $private = \Input::get('private') ? \Input::get('private') : \Input::get('secret');

            $application = Models\Application::where('public', '=', $public)->first();

            if (!$application || $application->private !== $private) {
                \App::abort(401);
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

    protected function requirePermission($or = [])
    {
        $this->setApplicationOrFail();
        foreach ($this->or as $permission) {
            if (in_array(trim($permission), $this->permissions)) {
                return true;
            }
        }
        \App::abort(401);
    }
} 
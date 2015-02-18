<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class ApplicationsController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/applications/index');
    }

    public function postNew()
    {
        $application = new Models\Application;
        $application->name = \Input::get('name');
        $application->description = \Input::get('description');
        $application->save();

        \Session::flash('status_message', 'Application created.');

        return \Redirect::to('/tools/applications');
    }

    public function getEdit()
    {
        $application = \Route::input('application');

        if ($application->admin_username !== Models\User::me()->username && !Models\User::me()->is_admin) {
            \App::abort(401);
        }

        return \View::make('tools/applications/edit', [
            'application' => $application
        ]);
    }

    public function postEdit()
    {
        $application = \Route::input('application');

        if ($application->admin_username !== Models\User::me()->username && !Models\User::me()->is_admin) {
            \App::abort(401);
        }

        $application->name = \Input::get('name');
        $application->description = \Input::get('description');

        if (Models\User::me()->is_admin) {
            $application->permission_admin = \Input::get('permission_admin') ? true : false;
            $application->permission_internal = \Input::get('permission_internal') ? true : false;
        }

        $application->save();

        \Session::flash('status_message', 'Application saved.');

        return \Redirect::to('/tools/applications/'.$application->public);
    }
}
<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;

class ManagersController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/managers', ['agreements' => Models\Agreement::all()]);
    }

    public function postIndex()
    {
        $ids = \Input::get('id');

        foreach ($ids as $id=>$settings) {
            $event = \CodeDay\Clear\Models\Batch\Event::where('id', '=', $id)
                ->where('batch_id', '=', Models\Batch::Managed()->id)
                ->first();

            if ($settings['manager_username']) {
                $user = Models\User::fromS5Username($settings['manager_username']);

                if ($user->username) {
                    $event->manager_username = $user->username;
                } else {
                    $event->manager_username = null;
                }
            } else {
                $event->manager_username = null;
            }

            if ($settings['coach_username']) {
                $user = Models\User::fromS5Username($settings['coach_username']);

                if ($user->username) {
                    $event->coach_username = $user->username;
                } else {
                    $event->coach_username = null;
                }
            } else {
                $event->coach_username = null;
            }

            $event->registration_estimate = $settings['registration_estimate'];

            if (isset($settings['agreement']) && $settings['agreement']) {
                $event->agreement_id = $settings['agreement'];
            } else {
                $event->agreement_id = null;
            }

            $event->save();
        }

        \Session::flash('status_message', 'Events updated');

        return \Redirect::to('/batch/managers');
    }
} 

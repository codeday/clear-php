<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \Codeday\Clear\Models;

class EvangelistController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/evangelists', [
            'batch' => \CodeDay\Clear\Models\Batch::Managed()
        ]);
    }

    public function postIndex()
    {
        $batch = \CodeDay\Clear\Models\Batch::Managed();
        $regions = \Input::get('region');

        foreach ($regions as $region_id=>$settings) {
            $event = \CodeDay\Clear\Models\Batch\Event::where('region_id', '=', $region_id)
                ->where('batch_id', '=', $batch->id)
                ->first();

            if ($settings['evangelist_username']) {
                $user = Models\User::fromS5Username($settings['evangelist_username']);

                if ($user->username) {
                    $event->evangelist_username = $user->username;
                } else {
                    $event->evangelist_username = null;
                }
            } else {
                $event->evangelist_username = null;
            }

            $event->save();
        }

        \Session::flash('status_message', 'Evangelists saved');

        return \Redirect::to('/tools/evangelists');
    }
} 
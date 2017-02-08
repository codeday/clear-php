<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Batch;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class EvangelistController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('batch/evangelists', [
            'batch' => \CodeDay\Clear\Models\Batch::Managed()
        ]);
    }

    public function postIndex()
    {
        $batch = \CodeDay\Clear\Models\Batch::Managed();
        $ids = \Input::get('id');
        $send_emails = \Input::get('send_emails') ? true : false;

        foreach ($ids as $id=>$settings) {
            $event = \CodeDay\Clear\Models\Batch\Event::where('id', '=', $id)
                ->where('batch_id', '=', $batch->id)
                ->firstOrFail();

            if ($settings['evangelist_username']) {
                $user = Models\User::fromS5Username($settings['evangelist_username']);

                if ($user->username) {
                    // User has changed. Send the welcome email if requested.
                    if ($send_emails && ($event->evangelist_username != $user->username)) {
                        foreach ([$user->email, $user->internal_email] as $to) { // Send to personal AND corporate email
                            Services\Email::SendOnQueue(
                                'StudentRND Evangelism', 'evg@srnd.org',
                                $user->name, $to,
                                Models\Batch::Managed()->name.' Evangelism',
                                \View::make('emails/evangelist_text', ['user' => $user]),
                                \View::make('emails/evangelist_html', ['user' => $user]),
                                false
                            );
                        }
                    }

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

        return \Redirect::to('/batch/evangelists');
    }
} 

<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Checkin extends ApiController {
    public function __construct() {
        if (\Input::get('token')) {
            $user = Models\User::fromToken(\Input::get('token'));
            $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->first();

            if (!isset($user)) {
                \App::abort(403);
            }

            if (!isset($event)) {
                \App::abort(404);
            }
            
            if ($user->username != $event->manager_username
                && $user->username != $event->evangelist_username
                && !$event->isUserAllowed($user)
                && !$user->is_admin) {
                    \App::abort(403);
            }

            $this->permissions = ['admin'];
            $this->application = true;
        } else {
            $this->setApplicationOrFail();
        }
    }

    // POST /api/checkin
    // r={registration id}, check={in|out}, event={event id}, allow_missing?
    public function postIndex()
    {
        $this->requirePermission(['admin']);
        $event = Models\Batch\Event::where('id', '=', \Input::get('event'))->firstOrFail();
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->first();

        if (!$registration) {
            return json_encode((object)[
                'success' => false,
                'error' => 'Registration does not exist.'
            ]);
        }


        if ($registration->batches_event_id !== $event->id) {
            return json_encode((object)[
                'success' => false,
                'error' => 'Incorrect event ('.$registration->event->fullName.').'
            ]);
        }

        $check = \Input::get('check');
        $allowMissing = \Input::get('allow_missing') ? true : false;

        $hasParent = isset($registration->parent_email) || $registration->parent_no_info;
        $hasWaiver = isset($registration->waiver_pdf_link) || $registration->type !== 'student';

        $existing = isset($registration->checked_in_at) ? 'in' : 'out';

        if ($check === $existing) {
            return json_encode((object)[
                'success' => false,
                'error' => 'Already checked in',
                'registration' => ModelContracts\Registration::Model($registration, $this->permissions)
            ]);
        }

        if ($check === 'in') {
            if ($allowMissing || ($hasParent && $hasWaiver)) {
                $registration->checked_in_at = \Carbon\Carbon::now();
                $registration->save();
                \Event::fire('registration.checkin', $registration);
            } else {
                return json_encode((object)[
                    'success' => false,
                    'error' => 'Waiver or parent info missing',
                    'registration' => ModelContracts\Registration::Model($registration, $this->permissions)
                ]);
            }
        } elseif ($check === 'out') {
            $registration->checked_in_at = null;
            $registration->save();
            \Event::fire('registration.checkout', $registration);
        } else {
            \App::abort(405);
        }

        return json_encode((object)[
            'success' => true,
            'has_parent_info' => $hasParent,
            'has_waiver' => $hasWaiver,
            'registration' => ModelContracts\Registration::Model($registration, $this->permissions)
        ]);
    }
}

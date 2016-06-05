<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class Register extends \CodeDay\Clear\Http\Controller {
    protected $requiresApplication = false;

    public function getIndex() {}

    public function getPromotion()
    {
        $event = \Route::input('event');
        $promotion = Models\Batch\Event\Promotion::where('code', '=', strtoupper(\Input::get('code')))
            ->where('batches_event_id', '=', $event->id)
            ->first();

        $giftCard = Models\GiftCard::where('code', '=', strtoupper(\Input::get('code')))
            ->first();

        if ($promotion) {
            $json = [
                'discount' => floatval($promotion->percent_discount),
                'cost' => $promotion->event->cost * (1 - ($promotion->percent_discount / 100.0)),
                'remaining_uses' => $promotion->remaining_uses,
                'expired' => $promotion->expires_at ? $promotion->expires_at->isPast() : false,
                'type' => $promotion->type
            ];
        } elseif ($giftCard) {
            $json = [
                'discount' => 100,
                'cost' => 0,
                'remaining_uses' => $giftCard->is_used ? 0 : 1,
                'expired' => false,
                'type' => 'student'
            ];
        } else {
            \App::abort(404);
        }

        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        $response->setContent(json_encode($json));
        return $response;
    }


    public function optionsRegister()
    {
        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        return $response;
    }

    public function postRegister()
    {
        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        $response->setContent(json_encode($this->_postRegister()));
        return $response;
    }

    private function _postRegister()
    {
        $event = \Route::input('event');
        $promotion = Models\Batch\Event\Promotion::where('code', '=', strtoupper(\Input::get('code')))
            ->where('batches_event_id', '=', $event->id)
            ->first();
        $giftCard = Models\GiftCard::where('code', '=', strtoupper(\Input::get('code')))
            ->first();

        $quoted_price = floatval(\Input::get('quoted_price'));

        $first_names = \Input::get('first_names');
        $last_names = \Input::get('last_names');
        $emails = \Input::get('emails');

        $registrants = [];
        for ($i = 0; $i < count($emails); $i++) {
            $registrants[] = (object)[
                'first_name' => $first_names[$i],
                'last_name' => $last_names[$i],
                'email' => trim($emails[$i])
            ];
        }

        // Check if the cost is still the same
        $unit_cost = $event->cost;
        if ($promotion) {
            $unit_cost *= (1 - ($promotion->percent_discount / 100.0));
        } elseif ($giftCard) {
            $unit_cost = 0;
        }

        $total_cost = $unit_cost * count($registrants);

        if (number_format($total_cost, 2) != number_format($quoted_price, 2)) {
            return [
                'status' => 500,
                'error' => 'quote_mismatch',
                'message' => 'The total cost has changed from $'.number_format($quoted_price, 2).' to $'.number_format($total_cost, 2).' since you first loaded the page.'
            ];
        }


        // Check if the promotion is still valid
        if ($promotion) {
            if ($promotion->expires_at && $promotion->expires_at->isPast()) {
                return [
                    'status' => 500,
                    'error' => 'promotion_expired',
                    'message' => 'That promotional code is expired.'
                ];
            }

            if ($promotion->remaining_uses === 0) {
                return [
                    'status' => 500,
                    'error' => 'promotion_used',
                    'message' => 'That promotional code has already been used the maximum number of times.'
                ];
            } elseif ($promotion->remaining_uses != null && $promotion->remaining_uses < count($registrants)) {
                return [
                    'status' => 500,
                    'error' => 'promotion_overused',
                    'message' => 'You requested more tickets than that promotional code allows.'
                ];
            }
        } elseif ($giftCard) {
            if ($giftCard->is_used) {
                return [
                    'status' => 500,
                    'error' => 'giftcard_used',
                    'message' => 'That gift card has already been used.'
                ];
            } elseif (count($registrants) > 1) {
                return [
                    'status' => 500,
                    'error' => 'giftcard_overused',
                    'message' => 'That gift card can only be used for one ticket.'
                ];
            }
        }

        // Check if the event has room
        if (!$event->remaining_registrations === 0) {
            return [
                'status' => 500,
                'error' => 'sold_out',
                'message' => 'The event is sold out.'
            ];
        } else if ($event->remaining_registrations < count($registrants)) {
            return [
                'status' => 500,
                'error' => 'exceeds_capacity',
                'message' => 'The event cannot fit the requested number of participants.'
            ];
        }

        // Check if any of the registrants are banned
        foreach ($registrants as $registrant) {
            $ban = Models\Ban::GetBannedReasonOrNull($registrant->email);
            if ($ban) {
                return [
                    'status' => 500,
                    'error' => 'banned',
                    'message' => $ban->reason_text
                        . ($ban->expires_at ? '<br /><br />You will be able to register again after '
                            .date('F j, Y', $ban->expires_at->timestamp).'.' : '')
                        . '<br /><br />You must <a href="mailto:contact@studentrnd.org">contact us</a> if believe this'
                        . ' is incorrect; if you register under a different name or email you will be turned away at'
                        . ' the door without a refund.'
                ];
            }
        }

        \DB::beginTransaction(); // In case something goes wrong, we'll want to roll-back the insert.

        // Create the registrations in the database
        try {
            $registrations = array_map(function($registrant) use ($event) {
                return Services\Registration::CreateRegistrationRecord(
                    $event,
                    $registrant->first_name, $registrant->last_name,
                    $registrant->email,
                    "student"
                );
            }, $registrants);
        } catch (\Exception $ex) { // Some sort of database error
            \DB::rollBack();
            return [
                'status' => 500,
                'error' => 'database_error',
                'message' => 'There was an error processing your registration information. Your card was not charged.'
            ];
        }

        // Charge the card
        if ($total_cost > 0) {
            try {
                Services\Registration::ChargeCardForRegistrations($registrations, $total_cost, \Input::get('card_token'));
            } catch(\Stripe\Error\Card $e) { // Stripe declined
                $e_json = $e->getJsonBody();
                $error = $e_json['error'];
                \DB::rollBack();
                return [
                    'status' => 500,
                    'error' => 'declined',
                    'message' => $error['message']
                ];
            }
        }

        \DB::commit(); // Looks good

        // Send the confirmation emails
        foreach ($registrations as $registration) {
            try {
                if ($promotion) {
                    $registration->batches_events_promotion_id = $promotion->id;
                    $registration->type = $promotion->type;
                    $registration->save();
                } elseif ($giftCard) {
                    $giftCard->batches_events_registration_id = $registration->id;
                    $giftCard->save();
                }
            } catch (\Exception $ex) {}
            try {
                Services\Registration::SendTicketEmail($registration);
                Services\Registration::EnqueueSurveyEmail($registration);
            } catch (\Exception $ex) {}
        }

        return [
            'status' => 200,
            'ids' => array_map(function($reg) { return $reg->id; }, $registrations)
        ];
    }
}

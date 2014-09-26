<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Register extends \Controller {
    public function getIndex() {}

    public function getPromotion()
    {
        $event = \Route::input('event');
        $promotion = Models\Batch\Event\Promotion::where('code', '=', \Input::get('code'))
            ->where('batches_event_id', '=', $event->id)
            ->firstOrFail();


        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        $response->setContent(json_encode([
                'discount' => floatval($promotion->percent_discount),
                'cost' => $promotion->event->cost * (1 - ($promotion->percent_discount / 100.0)),
                'remaining_uses' => $promotion->remaining_uses,
                'expired' => $promotion->expires_at ? $promotion->expires_at->isPast() : false
            ]
        ));
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
        $promotion = Models\Batch\Event\Promotion::where('code', '=', \Input::get('code'))
            ->where('batches_event_id', '=', $event->id)
            ->first();

        $card_token = \Input::get('card_token');
        $quoted_price = intval(\Input::get('quoted_price'));

        $first_names = \Input::get('first_names');
        $last_names = \Input::get('last_names');
        $emails = \Input::get('emails');

        $registrants = [];
        for ($i = 0; $i < count($emails); $i++) {
            $registrants[] = (object)[
                'first_name' => $first_names[$i],
                'last_name' => $last_names[$i],
                'email' => $emails[$i]
            ];
        }

        // Check if the cost is still the same
        $unit_cost = $event->cost;
        if ($promotion) {
            $unit_cost *= (1 - ($promotion->percent_discount / 100.0));
        }

        $total_cost = $unit_cost * count($registrants);

        if ($total_cost != $quoted_price) {
            return [
                'status' => 500,
                'error' => 'quote_mismatch',
                'message' => 'The total cost has changed to $'.$total_cost.' since you first loaded the page.'
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

        // Create the charge
        \Stripe::setApiKey(\Config::get('stripe.secret'));

        try {
            $for_descriptor = implode(', ', array_map(function($e) { return $e->first_name.' '.$e->last_name; }, $registrants));

            $charge = null;
            if ($total_cost != 0) {
                $charge = \Stripe_Charge::create([
                    "amount" => $total_cost * 100, // in cents
                    "currency" => "usd",
                    "card"  => $card_token,
                    "description" => 'CodeDay '.$event->name.' Registration: '.$for_descriptor,
                    "statement_description" => "CODEDAY",
                    "receipt_email" => $registrants[0]->email,
                    "metadata" => [
                        "registrations_count" => count($registrants)
                    ]
                ]);
            }

            foreach ($registrants as $registrant) {
                $row = new Models\Batch\Event\Registration;
                $row->batches_event_id = $event->id;
                if ($charge) {
                    $row->stripe_id = $charge->id;
                }
                $row->amount_paid = $unit_cost;
                if ($promotion) {
                    $row->batches_events_promotion_id = $promotion->id;
                }
                $row->first_name = $registrant->first_name;
                $row->last_name = $registrant->last_name;
                $row->email = $registrant->email;
                $row->save();

                \Mail::send('emails/registration', [
                        'first_name' => $registrant->first_name,
                        'last_name' => $registrant->last_name,
                        'total_cost' => $total_cost,
                        'unit_cost' => $unit_cost
                    ], function($envelope) use ($registrant, $event) {
                        $envelope->from('contact@studentrnd.org', 'StudentRND');
                        $envelope->to($registrant->email, $registrant->first_name.' '.$registrant->last_name);
                        $envelope->subject('CodeDay '.$event->name);
                });

                return [
                    'status' => 200
                ];
            }

        } catch(\Stripe_CardError $e) {
            $e_json = $e->getJsonBody();
            $error = $e_json['error'];
            return [
                'status' => 500,
                'error' => 'declined',
                'message' => $error['message']
            ];
        }
    }
} 

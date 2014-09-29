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

        $quoted_price = floatval(\Input::get('quoted_price'));

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
        try {
            $charge = null;
            if ($total_cost != 0) {
                $charge = $this->processCard($total_cost, $registrants);
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

        // Create registration rows:
        // We'll try to create each registrant in the database. In the event that something
        // goes wrong, we'll roll it all back.
        \DB::beginTransaction();
        foreach ($registrants as $registrant) {
            try {
                $this->processRegistration($unit_cost, $registrant, $charge, $promotion);
            } catch (\Exception $ex) {
                \DB::rollBack();

                if ($charge) {
                    $charge->refunds->create();
                }

                return [
                    'status' => 500,
                    'error' => 'database_error',
                    'message' => 'There was an error processing your registration information. The charge on your card was cancelled.'
                ];
            }
        }
        \DB::commit();

        // Try to send confirmation emails
        foreach ($registrants as $registrant) {
            try {
                $this->sendEmail($unit_cost, $total_cost, $registrant);
            } catch (\Exception $ex) {}
        }

        return [
            'status' => 200
        ];
    }

    private function processCard($total_cost, $registrants)
    {
        $event = \Route::input('event');
        $card_token = \Input::get('card_token');
        $for_descriptor = implode(', ', array_map(function($e) {
                return $e->first_name.' '.$e->last_name;
            }, $registrants));

        \Stripe::setApiKey(\Config::get('stripe.secret'));
        return \Stripe_Charge::create([
            "amount" => $total_cost * 100, // in cents
            "currency" => "usd",
            "card"  => $card_token,
            "description" => 'CodeDay '.$event->name.' Registration: '.$for_descriptor,
            "statement_description" => "CODEDAY",
            "metadata" => [
                "registrations_count" => count($registrants)
            ]
        ]);
    }

    private function processRegistration($unit_cost, $registrant_info, $charge = null, $promotion = null)
    {
        $event = \Route::input('event');

        $row = new Models\Batch\Event\Registration;
        $row->batches_event_id = $event->id;
        if ($charge) {
            $row->stripe_id = $charge->id;
        }
        $row->amount_paid = $unit_cost;
        if ($promotion) {
            $row->batches_events_promotion_id = $promotion->id;
        }
        $row->first_name = $registrant_info->first_name;
        $row->last_name = $registrant_info->last_name;
        $row->email = $registrant_info->email;
        $row->save();
    }

    private function sendEmail($unit_cost, $total_cost, $registrant_info)
    {
        $event = \Route::input('event');

        \Mail::send('emails/registration', [
            'first_name' => $registrant_info->first_name,
            'last_name' => $registrant_info->last_name,
            'total_cost' => $total_cost,
            'unit_cost' => $unit_cost
        ], function($envelope) use ($registrant_info, $event) {
            $envelope->from('contact@studentrnd.org', 'StudentRND');
            $envelope->to($registrant_info->email, $registrant_info->first_name.' '.$registrant_info->last_name);
            $envelope->subject('CodeDay '.$event->name);
        });
    }

} 

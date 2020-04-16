<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;
use CodeDay\Clear\Exceptions;

use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Models\Batch\Event\Promotion;
use CodeDay\Clear\Models\GiftCard;

class Register extends \CodeDay\Clear\Http\Controller {
    protected $requiresApplication = false;

    public function getIndex() { return self::getCorsRequest(); }

    public function getPromotion() { return self::getCorsRequest(json_encode($this->_getPromotion())); }
    private function _getPromotion()
    {
        $event = \Route::input('event');
        $giftcard = GiftCard::where('code', '=', strtoupper(\Input::get('promo')))->first();
        $promotion = Promotion::where('code', '=', strtoupper(\Input::get('promo')))
            ->where('batches_event_id', '=', $event->id)
            ->first();

        if ($promotion) {
            $cost = $promotion->event->cost;
            $discount = 0;
            if (isset($promotion->force_price)) {
                $cost = floatval($promotion->force_price);
                $discount = (1-($cost/$promotion->event->cost))*100;
            } elseif (isset($promotion->percent_discount)) {
                $discount = floatval($promotion->percent_discount);
                $cost *= (1 - ($promotion->percent_discount / 100.0));
            } else \abort(404);

            return [
                'discount' => $discount,
                'cost' => $cost,
                'remaining_uses' => $promotion->remaining_uses,
                'expired' => $promotion->expires_at ? $promotion->expires_at->isPast() : false,
                'type' => $promotion->type
            ];
        } elseif ($giftcard)
            return [
                'discount' => 100,
                'cost' => 0,
                'remaining_uses' => $giftcard->is_used ? 0 : 1,
                'expired' => false,
                'type' => 'student'
            ];
        else
            \abort(404);
    }

    public function optionsRegister() { return self::getCorsRequest(); }
    public function postRegister() {
        try {
            return self::getCorsRequest(json_encode($this->_postRegister()));
        } catch (\Exception $ex) {
            return self::apiThrow('generic', \config('app.debug') ? self::getFriendlyEx($ex) : null);
        }
    }
    private function _postRegister()
    {
        $event = \Route::input('event');
        $registrants = $this->zipRegistrants(\Input::get('first_names'), \Input::get('last_names'), \Input::get('emails'));
        $count = count($registrants);

        $giftcard = Models\GiftCard::where('code', '=', strtoupper(\Input::get('promo')))->first();
        $promotion = Models\Batch\Event\Promotion::where('code', '=', strtoupper(\Input::get('promo')))
            ->where('batches_event_id', '=', $event->id)
            ->first();

        if (\Input::get('promo') && !isset($giftcard) && !isset($promotion))
            return self::apiThrow('promo', 'The promotion code you entered was invalid.');

        $quote = floatval(\Input::get('quoted_price'));
        $quoteTax = floatval(\Input::get('quoted_tax'));
        $cost = $this->getCurrentCost($event, count($registrants), $promotion, $giftcard);
        $tax = $this->getCurrentTax($event, $cost);

        //
        // Validate and verify
        // (all methods return null if success, otherwise an error)
        //
        if ($e = self::verifyQuote($quote, $cost, $quoteTax, $tax)) return $e;
        if ($e = self::verifyCapacity($event, $count)) return $e;
        if ($promotion && $e = self::validatePromo($promotion, $count)) return $e;
        if ($giftcard && $e = self::validateGiftcard($giftcard, $count)) return $e;

        //
        // Process
        //
        if ($e = $this->doRegistration($registrants, $event, $promotion, $giftcard, $cost, $tax)) return $e;
    }

    /**
     * Registers the users, and charges the card.
     *
     * @param string[][] $registrants   Array of arrays of who to register, containing first_name, last_name, email
     * @param Event? $event             The event to register for.
     * @param Promotion? $promotion     The promotion to apply, if any.
     * @param GiftCard? $giftcard       The giftcard to use, if any.
     * @param float? $cost              The amount to charge the card, if any, not including sales tax.
     * @param float? $tax               The amount of sales tax to charge the card, if any.
     * @return object?                  An error message to return, if the registration failed. Otherwise, null.
     */
    private function doRegistration($registrants, Event $event, $promotion = null, $giftcard = null, float $cost = 0.0, float $tax = 0.0)
    {
        \DB::beginTransaction(); // In case something goes wrong, we'll want to roll-back the insert.
        $registrations = [];
        try {

            // We need to validate that emails exist, because CreateRegistrationRecord allows null emails (some school
            // partners don't give us emails for students). Registrations created through the web interface need to
            // have an email, as we have no other way to get in touch.
            foreach($registrants as $registrant) {
                if (!trim($registrant->email))
                    throw new Exceptions\Registration\InvalidValue(
                        sprintf("%s does not look like an email", $registrant->email)
                    );
            }

            $registrations = array_map(function($registrant) use ($event, $promotion, $giftcard) {
                //
                // Create the registrations
                //
                $registration = Services\Registration::CreateRegistrationRecord(
                    $event,
                    $registrant->first_name, $registrant->last_name,
                    $registrant->email,
                    (isset($promotion) && isset($promotion->type)) ? $promotion->type : "student"
                );

                //
                // Mark promotions/giftcards applied
                // (but do not error in case anything weird happens)
                //
                if (isset($promotion)) {
                    $registration->batches_events_promotion_id = $promotion->id;
                    $registration->save();
                } elseif (isset($giftcard)) {
                    $giftcard->batches_events_registration_id = $registration->id;
                    $giftcard->save();
                }

                return $registration;
            }, $registrants);


        //
        // Error: User was banned
        //
        } catch (Exceptions\Registration\Banned $ex) {
            \DB::rollBack();
            $expiresText = ($ex->Ban->expires_at ? "until ".date('F j, Y', $ex->Ban->expires_at) : "indefinitely");
            return self::apiThrow('banned',
                sprintf(
                    "%s This ban is effective %s. YOU MUST CONTACT US IF YOU BELIEVE THIS IS INCORRECT. If you "
                    ."register with a different name or email, you will be turned away at the door without a refund.",
                    $ex->Ban->reason_text, $expiresText
                )
            );

        //
        // Error: Invalid value
        //
        } catch (Exceptions\Registration\InvalidValue $ex) {
            \DB::rollBack();
            return self::apiThrow('missing_info', $ex->getMessage());

        //
        // Error: Missing field
        //
        } catch (Exceptions\Registration\MissingRequiredField $ex) {
            \DB::rollBack();
            return self::apiThrow('missing_info', 'All fields are required.');

        //
        // Error: ???
        //
        } catch (\Exception $ex) {
            \DB::rollBack();
            return self::apiThrow('generic', \config('app.debug') ? self::getFriendlyEx($ex) : null);
            throw $ex;
        }

        //
        // Done! Now we try to charge the card, and then commit the database.
        //
        if ($cost > 0 && $e = $this->doCharge($registrations, $cost, $tax)) return $e;
        \DB::commit();

        //
        // SUCCESS    (https://www.youtube.com/watch?v=jGoRYCbnVDg)
        //
        return [
            'status' => 200,
            'ids' => array_map(function($reg) { return $reg->id; }, $registrations)
        ];
    }

    /**
     * Tries to charge the user.
     *
     * If a card is declined we will pretend it was approved, because it's likely they could not afford the charge
     * (would qualify for a scholarship) or are being rejected for AVS/Radar (no other way to pay).
     *
     * @param Registration[] $registrations     The registrations for which the user should be charged.
     * @param float $cost                       The total to charge, not including sales tax.
     * @param float $tax                        The total amount of sales tax.
     */
    private function doCharge($registrations, float $cost, float $tax)
    {
        try {
            if(\Input::get('card_token'))
                Services\Registration::ChargeCardForRegistrations($registrations, $cost, $tax, \Input::get('card_token'));
            elseif(\Input::get('bitcoin_source'))
                Services\Registration::ChargeBitcoinSourceForRegistrations($registrations, $cost, $tax, \Input::get('bitcoin_source'));
            elseif($cost > 0)
                return self::apiThrow('payment', 'Payment is required');
        } catch(\Stripe\Error\Card $e) { }
          catch (\Exception $ex) {
            return self::apiThrow("api", $ex->getMessage());
          }
    }

    /**
     * Verifies that the quoted price is current (or is greater than the current cost).
     *
     * @param float $quote      The quoted cost.
     * @param float $cost       The actual cost
     * @param float $taxQuote   The quoted tax.
     * @param float $tax        The actual tax.
     * @return object?          If the quote is less than the cost, an error message to return. Otherwise, null.
     */
    private static function verifyQuote(float $quote, float $cost, float $taxQuote, float $tax)
    {
        // Make sure the cost is the same, using float-error-safe subtraction method.
        // (We will only throw an error if the difference is not in the user's favor.)
        //
        // eg [cost: $20.00] -  [quote: $10.00]  = $10 > 0.01
        if ((floatval($cost) - floatval($quote)) > 0.01)
            return self::apiThrow("quote_mismatch", sprintf(
                'The ticket cost changed from $%s to $%s. (This probably means the early-bird special just ended.)',
                        number_format($quote, 2), number_format($cost, 2)
            ));

        // Make sure the tax is the same.
        if ((floatval($taxQuote) - floatval($tax)) > 0.01)
            return self::apiThrow("quote_mismatch", sprintf(
                'The sales tax changed from $%s to $%s.',
                    number_format($taxQuote, 2), number_format($tax, 2)
            ));

        return null;
    }

    /**
     * Verifies that the event can hold the requested number of participants.
     *
     * @param Event $event  The event to which the registration is desired.
     * @param int $count    The number who want to register.
     * @return object?      If the event does not have capacity, an error message to return. Otherwise, null.
     */
    private static function verifyCapacity(Event $event, int $count)
    {
        if (!$event->batch->is_loaded || !$event->AllowRegistrationsCalculated)
            return self::apiThrow('capacity', "Sorry, this event is not accepting new registrations right now.");
        elseif (!$event->remaining_registrations === 0)
            return self::apiThrow('capacity', "Sorry, this event is now sold out.");
        elseif ($event->remaining_registrations < $count)
            return self::apiThrow('capacity', "This event is almost sold-out, and can't fit that many people.");
    }

    /**
     * Validates that the provided promotion code is not expired and supports the requested number of registrations.
     *
     * @param Promotion $promo  The promotion code to use.
     * @param int $count        The number of registrants to use this code.
     * @return object?          If the promo code cannot be used, an error message to return. Otherwise, null.
     */
    private static function validatePromo(Promotion $promotion, int $count)
    {
        $promoError = false;
        if ($promotion->expires_at && $promotion->expires_at->isPast())
            $promoError = "has expired";
        elseif ($promotion->remaining_uses === 0)
            $promoError = "has been used too many times";
        elseif ($promotion->remaining_uses != null && $promotion->remaining_uses < $count)
            $promoError = sprintf("only allows %s more uses, and you tried to register %s people",
                                    $promotion->remaining_uses, count($registrants));
        elseif (!isset($promotion->percent_discount) && !isset($promotion->force_price))
            $promoError = sprintf("does not provide a discount");

        if ($promoError) return self::apiThrow('promo', sprintf("Sorry, the code %s %s.", $promotion->code, $promoError));

        return null;
    }


    /**
     * Validates that the giftcard has not been used (and that only one person is trying to register).
     *
     * @param GiftCard $giftcard    The giftcard to use.
     * @param int $count            The number of registrants to use this code. If this is >1, this will throw an error.
     * @return object?              If the giftcard cannot be used, an error message to return. Otherwise, null.
     */
    private static function validateGiftcard(GiftCard $giftcard, int $count)
    {
        if ($giftcard->is_used)
            return self::apiThrow('giftcard', "That giftcard has already been used.");
        elseif ($count > 1)
            return self::apiThrow('giftcard',
                "Giftcards are only valid for one ticket. (To use more than one, you'll need to place multiple orders.)");

        return null;
    }

    /**
     * Gets the current quote.
     *
     * @param Event $event          The event for which people are registering.
     * @param int $count            The number of registrants,
     * @param Promotion? $promotion The promotion code, if any, to use.
     * @param Giftcard? $giftcard   The giftcard, if any, to use.
     * @return float                The current cost.
     */
    private static function getCurrentCost(Event $event, int $count, $promotion = null, $giftcard = null) : float
    {
        $normalCost = floatval($event->cost * $count);

        if ($giftcard)
            return 0.0;
        elseif ($promotion) {
            if (isset($promotion->force_price)) return floatval($promotion->force_price) * $count;
            elseif (isset($promotion->percent_discount)) return $normalCost * (1 - ($promotion->percent_discount / 100.0));
        }

        return $normalCost;
    }

    /**
     * Gets the current amount of sales tax which should be paid.
     *
     * @param Event $event      The event for which people are registering.
     * @param float $cost       The order total, minus tax.
     * @return float            The current tax amount.
     */
    private static function getCurrentTax(Event $event, float $cost)
    {
        return $cost * $event->sales_tax_rate;
    }


    /**
     * Returns an error message to the client. Does NOT automatically return this to the client!
     *
     * @param string $error     The error class, for use by programs (e.g. to highlight an error).
     * @param string $message   The full error message, for display to users.
     * @return object           The result, to return to the browser.
     */
    private static function apiThrow(string $error = "generic",
        string $message = "An unusual error occurred. Your card was not charged, but please contact us before trying again.")
    {
        return [
            'status' => 500,
            'error' => $error,
            'message' => $message
        ];
    }

    /**
     * Combines three lists (of first and last names and emails) into one array of associative arrays.
     *
     * @param string[] $firstNames  The registrant first names.
     * @param string[] $lastNames   The registrant last names.
     * @param string[] $emails      The registrant emails.
     * @return string[][]           Array of associative arrays ([[first_name => ...], ...])
     */
    private static function zipRegistrants($firstNames, $lastNames, $emails)
    {
        $registrants = [];

        for ($i = 0; $i < count($emails); $i++) {
            $registrants[] = (object)[
                'first_name' => $firstNames[$i],
                'last_name' => $lastNames[$i],
                'email' => trim($emails[$i])
            ];
        }

        return $registrants;
    }

    /**
     * Gets a CORS-enabled request.
     *
     * @param string? $content              Optional, request content.
     * @return Illuminate\Http\Response     CORS-enabled request.
     */
    private static function getCorsRequest($content = null)
    {
        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        if (isset($content))
            $response->setContent($content);
        return $response;
    }

    private static function getFriendlyEx(\Exception $ex)
    {
        return sprintf("%s (%s): %s)", $ex->getFile(), $ex->getLine(), $ex->getMessage());
    }
}

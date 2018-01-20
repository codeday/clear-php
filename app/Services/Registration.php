<?php
namespace CodeDay\Clear\Services;

use Carbon\Carbon;
use CodeDay\Clear\Models;
use CodeDay\Clear\Services;
use CodeDay\Clear\Exceptions;
use CodeDay\Clear\ModelContracts;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Registration CRUD interface for front-end registration flows.
 *
 * The Registration service has methods for creating, updating, and deleting registrations, as well as dispatching
 * related events (such as enqueuing a pre-event survey email). It's intended to be an interface for most front-end
 * registration tasks.
 *
 * @package     CodeDay\Clear\Services
 * @author      Tyler Menezes <tylermenezes@studentrnd.org>
 * @copyright   (c) 2014-2015 StudentRND
 * @license     Perl Artistic License 2.0
 */
class Registration {
    public static function CreateRegistrationRecord(Models\Batch\Event $event,
        string $firstName, string $lastName, $email = null, $type = null)
    {
        // Sanitize info
        $firstName = self::sanitizeField('first_name', $firstName);
        $lastName = self::sanitizeField('first_name', $lastName);
        $email = self::sanitizeField('email', $email);
        $type = self::sanitizeField('type', $type);

        // Set defaults for optional info
        if (!$type) $type = 'student';
        if (!$email) $email = null;

        if (!$firstName || !$lastName)
            throw new Exceptions\Registration\MissingRequiredField(
                sprintf("First Name and Last Name are required for all registrations (got %s, %s, %s)",
                        $firstName, $lastName, $email)
            );

        // Create the registration
        $reg = new Models\Batch\Event\Registration;
        $reg->batches_event_id = $event->id;
        $reg->first_name = $firstName;
        $reg->last_name = $lastName;
        $reg->email = strtolower($email);
        $reg->type = $type;
        $reg->save();


        // TODO(@tylermenezes): This should be moved into an event handler (cc @tjhorner)
        try {
            $past_registrations = Models\Batch\Event\Registration::orderBy('created_at', 'desc')->where('email', $email)->get();
            if(count($past_registrations) > 0) {
                foreach($past_registrations as $previous_registration) {
                    $devices = $previous_registration->devices;
                    
                    foreach($devices as $device) {
                        if($device->service == "app") {
                            Firebase::SendMessage([
                                "type" => "sign_in",
                                "id" => $reg->id
                            ], $device->token);
                        }
                    }
                }
            }
        } catch (\Exception $ex) {}

        \Event::fire('registration.register', [ModelContracts\Registration::Model($reg, ['admin', 'internal'])]);
        return $reg;
    }

    public static function RegisterGroup($allRegistrations, Models\Batch\Event $event, bool $allowEventOverride = false)
    {
        $createdRegistrations = [];
        $exceptions = [];
        foreach ($allRegistrations as $registration) {
            try {
                // Make sure we have all the required fields.
                //
                // This will be checked again in CreateRegistrationRecord, but the error message there won't contain
                // any reference to which line threw the error.
                $requiredFields = ['first_name', 'last_name'];
                foreach ($requiredFields as $field)
                    if (!isset($registration[$field]) || !trim($registration[$field]))
                        throw new Exceptions\Registration\MissingRequiredField(
                            sprintf("%s is required (%s)", $field, json_encode($registration))
                        );

                // Get the event to register into
                $regEvent = $event;
                if ($allowEventOverride) {
                    try {
                        if (isset($registration['batches_event_id'])) {
                            $regEvent = Models\Batch\Event::where('id', '=', $regEvent)->firstOrFail();
                        } else if (isset($registration['webname'])) {
                            $regEvent = (Models\User::IsLoggedIn() ? Models\Batch::Managed() : Models\Batch::Loaded())
                                            ->EventWithWebname($registration['webname']);
                        }
                    } catch (ModelNotFoundException $ex) {
                        throw new Exceptions\Registration\InvalidValue(
                            sprintf("Could not find event (%s)", json_encode($registration))
                        );
                    }
                }
                    
                // Create the registration
                $createdRegistration = self::CreateRegistrationRecord(
                    $regEvent, $registration['first_name'], $registration['last_name'],
                    $registration['email'] ?? null, $registration['type'] ?? null
                );

                // Group registrations often include all required information, so we'll set the additional info here.
                $otherKnownFields = [
                    'parent_name', 'parent_email', 'parent_phone', 'parent_secondary_phone', 'phone', 'request_loaner'
                ];
                foreach ($otherKnownFields as $field)
                    if (isset($registration[$field]))
                        $createdRegistration->$field = self::sanitizeField($field, $registration[$field]);

                $createdRegistrations[] = $createdRegistration;
            } catch (\Exception $ex) {
                $exceptions[] = $ex;
            }
        }

        // Return a container object containing the created registrations, as well as any exceptions which occurred
        // during registration.
        return (object)[
            'Registrations' => $createdRegistrations,
            'Exceptions' => $exceptions
        ];
    }

    public static function ChargeCardForRegistrations($registrations, $totalCost, $cardToken)
    {
        $event = $registrations[0]->event;

        // Build the description for Stripe
        $forDescriptor = implode(', ', array_map(function($reg) {
            return $reg->name;
        }, $registrations));
        $forDescriptor = 'CodeDay '.$event->name. ' Registration:'.$forDescriptor;

        // Make the charge
        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe\Charge::create([
            'currency' => 'usd',
            'statement_description' => 'CODEDAY',

            'amount' => intval($totalCost * 100),
            'card' => $cardToken,
            'description' => $forDescriptor,
            'metadata' => [
                'registrations_count' => count($registrations),
                'region' => $event->webname
            ]
        ]);

        $stripeFee = ($totalCost * 0.027) + 0.30;
        $amountReceived = $totalCost - $stripeFee;

        // Update the registrants' billing status
        foreach ($registrations as $reg) {
            $reg->stripe_id = $charge->id;
            $reg->amount_paid = $totalCost / count($registrations);
            $reg->is_earlybird_pricing = $reg->event->is_earlybird_pricing;
            $reg->save();
        }
    }

    public static function ChargeBitcoinSourceForRegistrations($registrations, $totalCost, $source)
    {
        $event = $registrations[0]->event;

        // Build the description for Stripe
        $forDescriptor = implode(', ', array_map(function($reg) {
            return $reg->name;
        }, $registrations));

        $forDescriptor = 'CodeDay '.$event->name. ' Registration: '.$forDescriptor;

        // Make the charge
        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe\Charge::create([
            'currency' => 'usd',
            'amount' => intval($totalCost * 100),
            'source' => $source,
            'description' => $forDescriptor,
            'metadata' => [
                'registrations_count' => count($registrations),
                'region' => $event->webname
            ]
        ]);

        $stripeFee = ($totalCost * 0.027) + 0.30;
        $amountReceived = $totalCost - $stripeFee;

        // Update the registrants' billing status
        foreach ($registrations as $reg) {
            $reg->stripe_id = $charge->id;
            $reg->amount_paid = $totalCost / count($registrations);
            $reg->is_earlybird_pricing = $reg->event->is_earlybird_pricing;
            $reg->save();
        }
    }

    public static function CancelRegistration(Models\Batch\Event\Registration $registration,
                                              $andRefund = true, $cancelRelated = false)
    {
        if ($andRefund && $registration->stripe_id) {
            \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
            $charge = \Stripe\Charge::retrieve($registration->stripe_id);

            if ($cancelRelated || count($registration->all_in_order) == 1) {
                $charge->refunds->create();
            } else {
                $charge->refunds->create([
                    'amount' => $registration->amount_paid * 100
                ]);
            }
        }

        $registration->amount_refunded += $registration->amount_paid;
        $registration->amount_paid = 0;
        $registration->save();

        $all_in_order = $registration->all_in_order;
        $registration->delete();

        if ($cancelRelated) {
            foreach ($all_in_order as $reg) {
                self::CancelRegistration($reg, false, false);
            }
        }
    }

    public static function PartiallyRefundRegistration(Models\Batch\Event\Registration $registration, $refundAmount)
    {
        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $charge = \Stripe\Charge::retrieve($registration->stripe_id);

        if ($registration->amount_paid == $refundAmount) {
            $charge->refunds->create();
        } elseif ($registration->amount_paid > $refundAmount) {
            $charge->refunds->create([
                'amount' => $refundAmount * 100
            ]);
        } else {
            throw new \Exception("Cannot refund more than the original ticket price.");
        }

        $registration->amount_paid -= $refundAmount;
        $registration->amount_refunded += $refundAmount;
        $registration->save();
    }

    public static function SendTicketEmail(Models\Batch\Event\Registration $reg)
    {
        // NOOP until we can fully remove this
    }

    public static function EnqueueSurveyEmail(Models\Batch\Event\Registration $reg)
    {
        // NOOP until we can fully remove this
    }

    public static function SendPartialRefundEmail(Models\Batch\Event\Registration $reg, $refundAmount)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Refunded: CodeDay '.$reg->event->name,
            \View::make('emails/registration/refund', [
                'registration' => $reg,
                'amount' => $refundAmount
            ])
        );
    }

    public static function SendCancelEmail(Models\Batch\Event\Registration $reg, $refund)
    {
        Services\Email::SendOnQueue(
            'CodeDay '.$reg->event->name, $reg->event->webname.'@codeday.org',
            $reg->name, $reg->email,
            'Ticket Cancelled: CodeDay '.$reg->event->name,
            \View::make('emails/registration/cancel', [
                'registration' => $reg,
                'refund' => $refund
            ])
        );
    }

    public static function GetCsv(Models\Batch\Event $event, $printHeader = true)
    {
        $registrations = iterator_to_array($event->registrations);

        // First, sort the registrations:
        usort($registrations, function($a, $b) {
            $typeSort = strcmp($a->type, $b->type);
            $lnameSort = strcmp($a->last_name, $b->last_name);
            $fnameSort = strcmp($b->first_name, $b->first_name);

            return $typeSort != 0 ? $typeSort : ($lnameSort != 0 ? $lnameSort : $fnameSort);
        });

        // Generate the header
        $header = [];
        if ($printHeader) {
            $header = [(object)[
                        'type' => 'type',
                        'last_name' => 'lastname',
                        'first_name' => 'firstname',
                        'email' => 'email',
                        'age' => 'age',
                        'promotion' => (object)['code' => 'promocode'],
                        'amount_paid' => 'paid',
                        'parent_name' => 'parentname',
                        'parent_email' => 'parentemail',
                        'parent_phone' => 'parentphone',
                        'parent_secondary_phone' => 'parentphonealt',
                        'checked_in_at' => 'checkedin',
                        'created_at' => 'created',
                        'event'     => (object)['webname' => 'event']]];
        }

        // Generate the file
        $content = implode("\n",
            array_map(function($reg) {
                return str_replace("\n", "", implode(',', [$reg->event->webname, $reg->type, $reg->last_name, $reg->first_name, $reg->email, $reg->age,
                    ($reg->promotion ? $reg->promotion->code : ''), $reg->amount_paid,
                    $reg->parent_name, $reg->parent_email, $reg->parent_phone, $reg->parent_secondary_phone,
                    $reg->checked_in_at, $reg->created_at]));
            }, array_merge($header, $registrations))
        );

        return $content;
    }

    public static function GetCsvMultiple($events)
    {
        $out = '';
        foreach ($events as $event) {
            $out .= self::GetCsv($event, strlen($out) === 0)."\n";
        }

        return $out;
    }

    /**
     * Sanatizes a value before saving it to the database.
     *
     * @param string $field The name of the field to sanatize.
     * @param mixed  $val   The value to sanatize.
     * @return mixed        Sanatized value.
     */
    private static function sanitizeField(string $field, $val)
    {
        // Propertly capitalize names
        $field_sanitizers = [
            'name' => function($val) { return self::fixNameCase($val); },
            'lower' => function($val) { return strtolower($val); },
            'bool' => function($val) {
                if (in_array(strtolower($val), ['true', 'yes', 'y', '1'])) return true;
                if (in_array(strtolower($val), ['false', 'no', 'n', '0', '-1'])) return false;
                return (bool)$val;
            },
            'phone' => function($val) {
                $phone = preg_replace('/\D+/', '', $val);
                if (strlen($phone) == 11) $phone = substr($phone, 1);
                return $phone;
            },
        ];

        $field_mappings = [
            'first_name' => 'name',
            'last_name' => 'name',
            'parent_name' => 'name',
            'email' => 'lower',
            'type' => 'lower',
            'parent_email' => 'lower',
            'parent_phone' => 'phone',
            'parent_secondary_phone' => 'phone',
            'phone' => 'phone',
            'request_loaner' => 'bool',
        ];

        if (isset($field_mappings[$field])) {
            $val = $field_sanitizers[$field_mappings[$field]](trim($val));
        }

        return $val;
    }

    /**
     * Properly capitalizes names
     *
     * From http://www.media-division.com/correct-name-capitalization-in-php/
     */
    private static function fixNameCase($string) 
    {
        $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
        $lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
        $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');
    
        $string = strtolower($string);
        foreach ($word_splitters as $delimiter)
        { 
            $words = explode($delimiter, $string); 
            $newwords = array(); 
            foreach ($words as $word)
            { 
                if (in_array(strtoupper($word), $uppercase_exceptions))
                    $word = strtoupper($word);
                else
                if (!in_array($word, $lowercase_exceptions))
                    $word = ucfirst($word); 
    
                $newwords[] = $word;
            }
    
            if (in_array(strtolower($delimiter), $lowercase_exceptions))
                $delimiter = strtolower($delimiter);
    
            $string = join($delimiter, $newwords); 
        } 
        return $string; 
    }

}

<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Registration extends ModelContract
{
    public static function getFields()
    {
        return [

            'id' => [
                'name'          => 'ID',
                'description'   => 'The ID of the registration; would be useful to e.g. generate a ticket barcode.',
                'example'       => 'q01pkMQc0dwb',
                'value'         => function($model) { return $model->id; }
            ],

            'email' => [
                'name'          => 'Email',
                'description'   => 'The email of the registrant.',
                'example'       => 'tylermenezes@studentrnd.org',
                'value'         => function($model) { return $model->email; }
            ],

            /* Name-Related Properties */
            'name' => [
                'name'          => 'Full Name',
                'description'   => 'The full name of the registrant.',
                'example'       => 'Tyler Menezes',
                'value'         => function($model) { return $model->name; }
            ],

            'first_name' => [
                'name'          => 'First Name',
                'description'   => 'The first name of the registrant.',
                'example'       => 'Tyler',
                'value'         => function($model) { return $model->first_name; }
            ],

            'last_name' => [
                'name'          => 'Last Name',
                'description'   => 'The last name of the registrant.',
                'example'       => 'Menezes',
                'value'         => function($model) { return $model->last_name; }
            ],

            /* Price-Related Properties */
            'amount_paid' => [
                'name'          => 'Amount Paid',
                'description'   => 'The amount the registrant paid for this registration, including any refunds.',
                'example'       => '7.00',
                'value'         => function($model) { return floatval($model->amount_paid); }
            ],

            'amount_refunded' => [
                'name'          => 'Amount Refunded',
                'description'   => 'The amount the registrant has been refunded for this ticket.',
                'example'       => '3.00',
                'value'         => function($model) { return floatval($model->amount_refunded); }
            ],

            'order_amount_paid' => [
                'name'          => 'Order Amount Paid',
                'description'   => 'The amount the registrant paid for this and all other tickets in the order.',
                'example'       => '17.00',
                'value'         => function($model) { return floatval($model->order_amount_paid); }
            ],

            'is_earlybird_pricing' => [
                'name'          => 'Is Earlybird Pricing',
                'description'   => 'True if the user received earlybird pricing, false otherwise.',
                'example'       => 'true',
                'value'         => function($model) { return boolval($model->is_earlybird_pricing); }
            ],

            /* Time-Related Properties */

            'registered_at' => [
                'name'          => 'Registered At',
                'description'   => 'Timestamp of when the user registered.',
                'example'       => '1415475800',
                'value'         => function($model) { return $model->registered_at; }
            ],

            'checked_in_at' => [
                'name'          => 'Checked In At',
                'description'   => 'The timestamp of when the user checked in, or null if the user has not checked in.',
                'example'       => '1415477800',
                'value'         => function($model) { return $model->checked_in_at; }
            ],

            /* Foreign */
            'event' => [
                'name'          => 'Event',
                'description'   => 'The event the user registered for.',
                'rich'          => true,
                'type'          => 'Event',
                'value'         => function($model, $permissions) {
                    return new Event($model->current_event, $permissions, true);
                }
            ],
        ];
    }
} 
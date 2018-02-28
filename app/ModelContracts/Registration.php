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

            'type' => [
                'name'          => 'Type',
                'description'   => 'String indicating what sort of registration this is. One of: student, teacher, volunteer, or sponsor.',
                'example'       => 'student',
                'value'         => function($model) { return $model->type; }
            ],

            'profile_image' => [
                'name'          => 'Profile Image',
                'description'   => 'URL of the registration\'s profile image.',
                'example'       => 'http://.../somerandomstring.jpg',
                'value'         => function($model) { return $model->profile_image_safe; }
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

            /* Parent-related properties */
            'parent_information_exempt' => [
                'name'          => 'Parent Information Exempt?',
                'description'   => 'Boolean which, if true, indicates that the student is over 18 and self-dependent.',
                'example'       => 'true',
                'value'         => function($model) { return boolval($model->parent_no_info); }
            ],

            'parent_name' => [
                'name'          => 'Parent Name',
                'description'   => 'Name of the parent, not normalized to any specific format. May be null.',
                'example'       => 'Catherine',
                'value'         => function($model) { return $model->parent_name; }
            ],

            'parent_email' => [
                'name'          => 'Parent Email',
                'description'   => 'Email of the parent. May be null.',
                'example'       => 'x@example.com',
                'value'         => function($model) { return $model->parent_email; }
            ],

            'parent_phone' => [
                'name'          => 'Parent Phone',
                'description'   => '10-digit parent contact number, including leading 1. May be null.',
                'example'       => '14257807901',
                'value'         => function($model) { return $model->parent_phone; }
            ],

            'parent_secondary_phone' => [
                'name'          => 'Parent Secondary Phone',
                'description'   => '10-digit alternate parent contact number, including leading 1. May be null.',
                'example'       => '14257807901',
                'value'         => function($model) { return $model->parent_secondary_phone; }
            ],

            'age' => [
                'name'          => 'Age of Attendee',
                'description'   => 'Age of the person attending the event.',
                'example'       => '13',
                'value'         => function($model) { return floatval($model->age); }
            ],
            
            'is_minor' => [
                'name'          => 'Is Minor',
                'description'   => 'True if the attendee is a minor in the venue jurisdiction.',
                'example'       => 'true',
                'value'         => function($model) { return $model->is_minor; }
            ],

            'request_loaner' => [
                'name'          => 'Request Loaner?',
                'description'   => 'If true, the person is requesting a loaner laptop.',
                'example'       => 'true',
                'value'         => function($model) { return boolval($model->request_loaner); }
            ],

            'waiver_pdf' => [
                'name'          => 'Waiver PDF',
                'description'   => 'The PDF of the completed, signed waiver.',
                'example'       => 'https://.../longid.pdf',
                'value'         => function($model) { return $model->waiver_pdf_link; }
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
                    return new Event($model->event, $permissions);
                }
            ],

            'devices' => [
                'name'          => 'Devices',
                'description'   => 'The attendee\'s devices.',
                'type'          => 'Device',
                'value'         => function($model, $permissions) {
                    return Device::Collection($model->devices, $permissions);
                }
            ],
        ];
    }
} 

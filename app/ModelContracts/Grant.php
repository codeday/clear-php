<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Grant extends ModelContract
{
    public static function getFields()
    {
        return [
            'id' => [
                'name'          => 'ID',
                'description'   => 'The ID of the grant. Numeric.',
                'example'       => '1',
                'value'         => function($model) { return $model->id; }
            ],

            'username' => [
                'name'          => 'Username',
                'description'   => 'Username of the user that this grant belongs to.',
                'example'       => 'tylermenezes',
                'value'         => function($model) { return $model->username; }
            ],

            'event' => [
                'name'          => 'Event',
                'description'   => 'The event that this grant gave the user access to.',
                'type'          => 'Event',
                'rich'          => true,
                'value'         => function($model, $permissions) { return new Event($model->event, $permissions, true); }
            ],

            'region' => [
                'name'          => 'Event',
                'description'   => 'The region of the event that this grant gave the user access to.',
                'type'          => 'Event',
                'rich'          => true,
                'value'         => function($model, $permissions) { return new Region($model->event->region, $permissions, true); }
            ]
        ];
    }
}

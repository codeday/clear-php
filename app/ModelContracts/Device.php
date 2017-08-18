<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Device extends ModelContract
{
    public static function getFields()
    {
        return [
            'id' => [
                'name'          => 'ID',
                'description'   => 'The internal ID of the device. Matches [0-9]+',
                'example'       => '1',
                'value'         => function($model) { return $model->id; }
            ],

            'service' => [
                'name'          => 'Service',
                'description'   => 'The service of the device. Can be one of sms, app, or messenger.',
                'example'       => 'sms',
                'value'         => function($model) { return $model->service; }
            ],

            'token' => [
                'name'          => 'Token',
                'description'   => 'The token of the device. Varies based on service type. Can be phone number, Firebase IID, or Facebook PSID.',
                'example'       => '17606771329',
                'requires'      => ['internal'],
                'value'         => function($model) { return $model->token; }
            ]
        ];
    }
} 
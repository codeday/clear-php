<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Application extends ModelContract
{
    public static function getFields()
    {
        return [
            'public' => [
                'name'          => 'Public',
                'description'   => 'Public key of application.',
                'example'       => 'testtesttesttesttesttest',
                'value'         => function($model) { return $model->public; }
            ],

            'private' => [
                'name'          => 'Private',
                'description'   => 'Private key of application.',
                'example'       => 'testtesttesttesttesttest',
                'value'         => function($model) { return $model->private; }
            ],

            'name' => [
                'name'          => 'Name',
                'description'   => 'Name of application, as defined by application owner.',
                'example'       => 'Test App',
                'value'         => function($model) { return $model->name; }
            ],

            'description' => [
                'name'          => 'Description',
                'description'   => 'Description of application, as defined by application owner.',
                'example'       => 'The best test app you\'ll find around!',
                'value'         => function($model) { return $model->description; }
            ],

            'permission_admin' => [
                'name'          => 'Admin permission',
                'description'   => '`true` if application has admin permissions.',
                'example'       => 'internal',
                'value'         => function($model) { return boolval($model->permission_admin); }
            ],

            'permission_internal' => [
                'name'          => 'Internal permission',
                'description'   => '`true` if application has internal permissions.',
                'example'       => 'internal',
                'value'         => function($model) { return boolval($model->permission_internal); }
            ]
        ];
    }
}

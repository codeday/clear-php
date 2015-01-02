<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class User extends ModelContract
{
    public static function getFields()
    {
        return [
            'username' => [
                'name'          => 'Username',
                'description'   => 'The s5 username of the user.',
                'example'       => 'tylermenezes',
                'value'         => function($model) {
                    return $model->username;
                }
            ],
            'name' => [
                'name'          => 'Name',
                'description'   => 'The full name of the user.',
                'example'       => 'Tyler Menezes',
                'value'         => function($model) {
                    return $model->name;
                }
            ],
            'first_name' => [
                'name'          => 'Name',
                'description'   => 'The first name of the user.',
                'example'       => 'Tyler',
                'value'         => function($model) {
                        return $model->first_name;
                }
            ],
            'last_name' => [
                'name'          => 'Name',
                'description'   => 'The last name of the user.',
                'example'       => 'Menezes',
                'value'         => function($model) {
                        return $model->last_name;
                }
            ],
            'email' => [
                'name'          => 'Email',
                'description'   => 'The email address of the user.',
                'example'       => 'tylermenezes@studentrnd.org',
                'value'         => function($model) {
                        return $model->email;
                }
            ],
            'is_admin' => [
                'name'          => 'Is Admin',
                'description'   => 'True if the user is an admin, false otherwise.',
                'value'         => function($model) {
                        return boolval($model->is_admin);
                }
            ],
            'phone' => [
                'name'          => 'Phone',
                'description'   => 'The phone number of the user.',
                'example'       => '14257807901',
                'value'         => function($model) {
                        return $model->phone;
                }
            ],
        ];
    }
} 
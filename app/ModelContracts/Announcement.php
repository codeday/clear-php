<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Announcement extends ModelContract
{
    public static function getFields()
    {
        return [
            'id' => [
                'name'          => 'ID',
                'description'   => 'The internal ID of the announcement. Matches [a-zA-Z0-9]{5,15}.',
                'example'       => 'toaehnaoteh',
                'value'         => function($model) { return $model->id; }
            ],

            'posted_at' => [
                'name'          => 'Posted At',
                'description'   => 'The date that this announcement was posted.',
                'example'       => '2017-07-19 20:13:01.000000',
                'value'         => function($model) { return $model->created_at; }
            ],

            'body' => [
                'name'          => 'Body',
                'description'   => 'Body text of the announcement.',
                'example'       => 'Everything is on fire, leave now!!',
                'value'         => function($model) { return $model->body; }
            ],

            'urgency' => [
                'name'          => 'Urgency',
                'description'   => 'The urgency of the announcement. Can be 1, 2, or 3.',
                'example'       => '3',
                'value'         => function ($model) { return $model->urgency; }
            ],

            'link' => [
                'name'          => 'Link',
                'description'   => 'The attached link, if any. Can be null.',
                'example'       => '{ "url": "https://codeday.org", "text": "CodeDay Website" }',
                'value'         => function ($model) { 
                    if($model->cta && $model->link) {
                        return (object)[
                            'url' => $model->link,
                            'text' => $model->cta
                        ];
                    } else {
                        return null;
                    }
                 }
            ],

            'creator' => [
                'name'          => 'Creator',
                'description'   => 'The user who created the announcement.',
                'type'          => 'User',
                'value'         => function ($model, $permissions) {
                    // special permissions for user!!!
                    return (object)[
                        'username' => $model->creator->username,
                        'name' => $model->creator->name
                    ];
                }
            ]
        ];
    }
} 
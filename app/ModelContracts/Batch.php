<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Batch extends ModelContract
{
    public static function getFields()
    {
        return [
            'id' => [
                'name'          => 'ID',
                'description'   => 'The internal ID of the batch. Matches [a-zA-Z0-9]{5,15}.',
                'example'       => 'toaehnaoteh',
                'value'         => function($model) { return $model->id; }
            ],

            'name' => [
                'name'          => 'Name',
                'description'   => 'The name of the batch. Usually the season name followed by the year.',
                'example'       => 'Fall 2014',
                'value'         => function($model) { return $model->name; }
            ],

            'starts_at' => [
                'name'          => 'Starts At',
                'description'   => 'The start date of the batch, in the format YYYY-MM-DD.',
                'example'       => '2014-11-08',
                'value'         => function ($model) { return $model->starts_at->format('Y-m-d'); }
            ],

            'ends_at' => [
                'name'          => 'Ends At',
                'description'   => 'The end date of the batch, in the format YYYY-MM-DD.',
                'example'       => '2014-11-09',
                'value'         => function ($model) { return $model->ends_at->format('Y-m-d'); }
            ],

            'is_loaded'=> [
                'name'          => 'Is Loaded',
                'description'   => 'If true, indicates that the batch is the one currently shown on the site. If false,'
                                 . ' it is either a future or past batch.',
                'value'         => function ($model) { return boolval($model->is_loaded); }
            ],

            'events' => [
                'name'          => 'Events',
                'description'   => 'A list of all events in the batch.',
                'type'          => 'Event',
                'rich'          => true,
                'value'         => function($model, $permissions) {
                        foreach ($model->events as $event) {
                            yield new Event($event, $permissions, true);
                        }
                    }
            ]
        ];
    }
} 
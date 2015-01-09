<?php
namespace CodeDay\Clear\ModelContracts;

use \CodeDay\Clear\Models;

class Region extends ModelContract
{
    public static function getFields()
    {
        return [
            'id' => [
                'name'          => 'ID',
                'description'   => 'The interal region ID. Equivalent of webname.',
                'example'       => 'sf',
                'value'         => function($model) {
                        return $model->id;
                    }
            ],

            'name' => [
                'name'          => 'Name',
                'description'   => 'The name of the region.',
                'example'       => 'San Francisco',
                'value'         => function($model) {
                    return $model->name;
                }
            ],

            'webname' => [
                'name'          => 'Web Name',
                'description'   => 'The name used on the website. Guaranteed to uniquely identify the region and'
                    . ' contain only [a-zA-Z0-9-]. Does not uniquely identify the event, only the region.',
                'example'       => 'sf',
                'value'         => function($model) {
                    return $model->webname;
                }
            ],

            'abbr' => [
                'name'          => 'Abbreviation',
                'description'   => 'An initialism or acronym identifying the region. Guaranteed to match [A-Z]{2,3}.'
                    . ' Does not uniquely identify the event or region. Can be, but is not guaranteed to'
                    . ' be, the airport code for the region.',
                'example'       => 'SF',
                'value'         => function($model) {
                    return $model->abbr;
                }
            ],

            'location' => [
                'name'          => 'Location',
                'description'   => 'An object containing lat and lng, each floats, describing the centroid of the'
                                 . ' region.',
                'example'       => '{ lat: 37.78, lng: -122.42 }',
                'value'         => function($model) {
                    return (object)[
                        'lat' => $model->lat,
                        'lng' => $model->lng
                    ];
                }
            ],

            'timezone' => [
                'name'          => 'Timezone',
                'description'   => 'The name of the timezone in which this region is held. Will be one of the regions'
                                 . ' in PECL\'s timezonedb.',
                'example'       => 'America/Los_Angeles',
                'value'         => function($model) {
                    return $model->timezone;
                }
            ],

            'timezone_group' => [
                'name'          => 'Timezone Group',
                'description'   => 'Mapping of the timezonedb timezone onto one of the four continental US timezones:'
                                 . ' Pacific, Mountain, Central, Eastern',
                'example'       => 'Pacific',
                'value'         => function($model) {
                    switch ($model->timezone) {
                        case 'America/Los_Angeles':
                            return 'Pacific';
                        case 'America/Denver':
                            return 'Mountain';
                        case 'America/Chicago':
                            return 'Central';
                        case 'America/Detroit':
                            return 'Eastern';
                    }

                    return null;
                }
            ],

            'current_event' => [
                'name'          => 'Current Event',
                'description'   => 'The batch-region mapping representing the currently loaded event in this region.',
                'type'          => 'Event',
                'value'         => function($model, $permissions, $sparse) {
                    return new Event($model->current_event, $permissions, true);
                }
            ]
        ];
    }
} 
<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Events extends ContractualController {
    protected $fields = [
        'id',
        'name',
        'webname',
        'abbr',
        'full_name',
        'registration_info' => [
            'estimate' => 'registration_estimate',
            'max' => 'max_registrations',
            'is_open' => 'allow_registrations_calculated',
            'remaining' => 'remaining_registrations',
            'is_earlybird_ending'
        ],
        'sponsors' => 'sponsors_info',
        'schedule',
        'custom_css',
        'is_early_bird_pricing',
        'cost',
        'stripe_public_key',
        'venue',
        'starts_at',
        'ends_at',
        'batch_name',
        'emergency_phone',
        'waiver' => 'waiver_link',
        'manager' => 'manager_username',
        'evangelist' => 'evangelist_username'
    ];

    public function getIndex()
    {
        return $this->getContract(Models\Batch::Loaded()->events);
    }

    public function getEvent()
    {
        return $this->getContract(\Route::input('event'));
    }
} 

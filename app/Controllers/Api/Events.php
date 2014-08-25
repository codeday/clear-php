<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Events extends ContractualController {
    protected $fields = [
        'id',
        'name',
        'full_name',
        'registration_info' => [
            'estimate' => 'registration_estimate',
            'max' => 'max_registrations',
            'is_open' => 'allow_registrations'
        ],
        'venue',
        'waiver' => 'waiver_link',
        'manager' => 'manager_username'
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
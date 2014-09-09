<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Batches extends ContractualController {
    protected $fields = [
        'id',
        'name',
        'starts_at',
        'events',
        'loaded' => 'is_loaded'
    ];

    public function getCurrent()
    {
        return $this->getContract(Models\Batch::Loaded());
    }
} 
<?php
namespace CodeDay\Clear\Models\Batch\Event\Registration;

use Illuminate\Database\Eloquent\SoftDeletes;
use CodeDay\Clear\Services;
use CodeDay\Clear\Jobs;
use Illuminate\Support\Facades\Bus;

class Device extends \Eloquent {
    protected $table = 'batches_events_registrations_devices';

    public function registration()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_events_registration_id', 'id');
    }
}

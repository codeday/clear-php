<?php
namespace CodeDay\Clear\Models\Batch;

use \Illuminate\Database\Eloquent;
use \Carbon\Carbon;

class Supply extends \Eloquent {
    protected $table = 'batches_supplies';

    public function batch()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch', 'batch_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }
}
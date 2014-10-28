<?php
namespace CodeDay\Clear\Models\Batch\Event;

class Sponsor extends \Eloquent {
    protected $table = 'batches_events_sponsors';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }
} 
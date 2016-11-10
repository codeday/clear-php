<?php
namespace CodeDay\Clear\Models\Batch\Event;


class SpecialLink extends \Eloquent {
    protected $table = 'batches_events_speciallinks';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }
}
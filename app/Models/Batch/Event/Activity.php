<?php
namespace CodeDay\Clear\Models\Batch\Event;

class Activity extends \Eloquent {
    protected $table = 'batches_events_activities';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function getDayAttribute()
    {
        return $this->timestamp->format('l');
    }

    public function getHourAttribute()
    {
        return $this->timestamp->format('g:ia');
    }

    public function getTimestampAttribute()
    {
        return $this->event->batch->starts_at->copy()->addHours(12)->addMinutes($this->time * 60);
    }
} 

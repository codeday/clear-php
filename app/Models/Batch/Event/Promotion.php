<?php
namespace CodeDay\Clear\Models\Batch\Event;

class Promotion extends \Eloquent {
    protected $table = 'batches_events_promotions';

    public function getIsValidAttribute()
    {
        return (
            !($this->allowed_uses && !$this->registrations->count() > $this->allowed_uses)
            && !($this->expires_at !== null && Carbon::now()->gt($this->expires_at))
        );
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'expires_at'];
    }

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function registrations()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_events_promotion_id', 'id');
    }
} 
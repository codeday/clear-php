<?php
namespace CodeDay\Clear\Models\Batch\Event;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Registration extends \Eloquent {
    protected $table = 'batches_events_registrations';

    use SoftDeletingTrait;

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function promotion()
    {
        return $this->hasOne('\CodeDay\Clear\Models\Batch\Event\Promotion', 'batches_events_promotion_id', 'id');
    }
}
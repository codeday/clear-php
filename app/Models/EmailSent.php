<?php
namespace CodeDay\Clear\Models;

class EmailSent extends \Eloquent {
    protected $table = 'emails_sent';

    public function region()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Region', 'region_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch', 'batch_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }
}
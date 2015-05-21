<?php
namespace CodeDay\Clear\Models\EvangelistSms;

use \Carbon\Carbon;

class Sent extends \Eloquent {
    protected $table = 'evangelist_sms_sent';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function evangelistSms()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\EvangelistSms', 'evangelist_sms_id', 'id');
    }
}
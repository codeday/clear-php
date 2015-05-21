<?php
namespace CodeDay\Clear\Models;

use \Carbon\Carbon;

class EvangelistSms extends \Eloquent {
    protected $table = 'evangelist_sms';

    public function wasSentToEvent(Batch\Event $event) {
        return EvangelistSms\Sent
            ::where('evangelist_sms_id', '=', $this->id)
            ->where('batches_event_id', '=', $event->id)
            ->exists();
    }
}
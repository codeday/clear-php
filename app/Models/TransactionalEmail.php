<?php
namespace CodeDay\Clear\Models;

use Illuminate\Database\Eloquent;

class TransactionalEmail extends \Eloquent {
    protected $table = 'transactional_emails';

    public function registration()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_event_registration_id', 'id');
    }
}

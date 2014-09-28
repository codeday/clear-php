<?php
namespace CodeDay\Clear\Models\User;

use Illuminate\Database\Eloquent;

class Grant extends \Eloquent {
    protected $table = 'users_grants';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'username', 'username');
    }
}
<?php
namespace CodeDay\Clear\Models;

class GiftCard extends \Eloquent {
    protected $table = 'giftcards';

    public function registration()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_events_registration_id', 'id');
    }

    public function getIsUsedAttribute()
    {
        return $this->registration !== null;
    }
}
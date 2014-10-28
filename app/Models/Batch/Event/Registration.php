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

    public function getEmailMd5Attribute()
    {
        return hash('md5', $this->email);
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getRelatedRegistrationsAttribute()
    {
        if (!$this->stripe_id) {
            return [];
        }

        return self::where('id', '!=', $this->id)
            ->where('stripe_id', '=', $this->stripe_id)
            ->get();
    }

    public function getAllInOrderAttribute()
    {
        if (!$this->stripe_id) {
            return [$this];
        }

        return self::where('stripe_id', '=', $this->stripe_id)
            ->get();
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'checked_in_at'];
    }

    public function promotion()
    {
        return $this->hasOne('\CodeDay\Clear\Models\Batch\Event\Promotion', 'batches_events_promotion_id', 'id');
    }
}
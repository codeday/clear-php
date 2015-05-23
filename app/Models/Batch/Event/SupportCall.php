<?php
namespace CodeDay\Clear\Models\Batch\Event;

use Illuminate\Database\Eloquent\SoftDeletingTrait;
use \CodeDay\Clear\Models;

class SupportCall extends \Eloquent {
    protected $table = 'batches_events_supportcalls';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function answeredBy()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'answered_by_username', 'username');
    }

    public function registration()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_events_registration_id', 'id');
    }

    public static function getRegistrationFromParentPhoneNumber($phone)
    {
        if (strlen($phone) < 11) {
            $phone = '1'.$phone;
        }

        return Models\Batch\Event\Registration
            ::select('batches_events_registrations.*')
            ->join('batches_events', 'batches_events_registrations.batches_event_id', '=', 'batches_events.id')
            ->where('batches_events.batch_id', '=', Models\Batch::Loaded()->id)
            ->where(function($sub) use ($phone) {
                return $sub
                    ->where('batches_events_registrations.parent_phone', '=', $phone)
                    ->orWhere('batches_events_registrations.parent_secondary_phone', '=', $phone);
            })
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $registration = static::getRegistrationFromParentPhoneNumber($model->caller);
            if ($registration) {
                $model->batches_events_registration_id = $registration->id;
            }
        });
    }
}

<?php
namespace CodeDay\Clear\Models\Batch\Event;

use Illuminate\Database\Eloquent\SoftDeletes;
use CodeDay\Clear\Services;
use CodeDay\Clear\Jobs;
use Illuminate\Support\Facades\Bus;

class Registration extends \Eloquent {
    protected $table = 'batches_events_registrations';
    public $incrementing = false;

    use SoftDeletes;

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function transactionalEmails()
    {
        return $this->hasMany('\CodeDay\Clear\Models\TransactionalEmail', 'batches_events_registration_id', 'id');
    }

    public function devices()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Registration\Device', 'batches_events_registration_id', 'id');
    }

    public function getEmailMd5Attribute()
    {
        return hash('md5', $this->email);
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getWaiverAttribute()
    {
        return Services\Waiver::get($this);
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

    public function getPreviousRegistrationsAttribute()
    {
        return self::where('email', '=', $this->email)->where('id', '!=', $this->id)->get();
    }

    public function getAllInOrderAttribute()
    {
        if (!$this->stripe_id) {
            return [$this];
        }

        return self::where('stripe_id', '=', $this->stripe_id)
            ->get();
    }

    public function getOrderAmountPaidAttribute()
    {
        $all_in_order = $this->all_in_order;
        if (!is_array($all_in_order)) {
            $all_in_order = iterator_to_array($all_in_order);
        }

        return array_reduce($all_in_order, function($a, $b) {
            return (object)['amount_paid' => $a->amount_paid + $b->amount_paid];
        }, (object)["amount_paid" => 0])->amount_paid;
    }

    public function getOrderAmountReceivedAttribute()
    {
        if (!$this->stripe_id || $this->order_amount_paid == 0) {
            return 0;
        }

        $stripe_fee = ($this->order_amount_paid * 0.027) + 0.30;
        return $this->order_amount_paid - $stripe_fee;
    }

    public function getAmountReceivedAttribute()
    {
        $paid_in_order = [];
        foreach ($this->all_in_order as $reg) {
            if ($reg->amount_paid > 0) {
                $paid_in_order[] = $reg;
            }
        }
        return $this->order_amount_received/count($paid_in_order);
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'checked_in_at', 'break_started_at'];
    }

    public function promotion()
    {
        return $this->hasOne('\CodeDay\Clear\Models\Batch\Event\Promotion', 'id', 'batches_events_promotion_id');
    }

    public function getProfileImageSafeAttribute()
    {
        return isset($this->profile_image)
                ? $this->profile_image
                : 'http://www.gravatar.com/avatar/'.hash('md5', $this->email).'?s=300&d=mm';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $id = null;
            do {
                $id = self::generateUnambiguousRandomString(15);
            } while (self::where('id', '=', $id)->exists());

            $model->{$model->getKeyName()} = $id;

            Bus::dispatch(new Jobs\SyncProfileImageJob($model));
        });
    }

    private static function generateUnambiguousRandomString($length = 10) {
        $characters = '234679abcdefghkmnpqruvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

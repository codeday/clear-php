<?php
namespace CodeDay\Clear\Models;

class Batch extends \Eloquent {
    protected $table = 'batches';
    public $incrementing = false;

    public function events()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'batch_id', 'id');
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'starts_at', 'reminder_email_sent_at', 'preevent_email_sent_at'];
    }

    public function getEndsAtAttribute()
    {
        return $this->starts_at->copy()->addDay();
    }

    public function getShipmentsAttribute()
    {
        $shipped_events = Batch\Event::whereNotNull('shipment_number')
            ->orderBy('shipment_number', 'ASC')
            ->groupBy('shipment_number')
            ->get();


        return array_map(function($e){
            return $e->shipment_number;
        }, iterator_to_array($shipped_events));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = str_random(12);
        });
    }

    public static function Loaded()
    {
        return self::where('is_loaded', '=', true)->first();
    }

    public static function Managed()
    {
        if (!\Session::get('managed_batch_id')) {
            \Session::set('managed_batch_id', self::Loaded()->id);
        }

        $batch = self::find(\Session::get('managed_batch_id'));
        if ((User::is_logged_in() && !User::me()->is_admin)
            && count(User::me()->getManagedEvents($batch)) == 0
            && count(User::me()->managed_batches) > 0) {
            $batches = User::me()->managed_batches;
            $most_recent_batch = $batches[count($batches) - 1];
            \Session::set('managed_batch_id', $most_recent_batch->id);
        }

        return self::find(\Session::get('managed_batch_id'));
    }

    public function manage()
    {
        \Session::set('managed_batch_id', $this->id);
    }

    public function has_region(Region $r) {
        foreach ($this->events as $event) {
            if ($event->region_id === $r->id) {
                return true;
            }
        }

        return false;
    }

    public function supplies()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Supply', 'batch_id', 'id');
    }
} 
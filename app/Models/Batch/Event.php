<?php
namespace CodeDay\Clear\Models\Batch;

use \Illuminate\Database\Eloquent;
use \Carbon\Carbon;

class Event extends \Eloquent {
    use Eloquent\SoftDeletingTrait;

    protected $table = 'batches_events';
    public $incrementing = false;

    public function getFullNameAttribute()
    {
        return 'CodeDay '.$this->region->name.' '.$this->batch->name;
    }

    public function region()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Region', 'region_id', 'id');
    }

    public function batch()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch', 'batch_id', 'id');
    }

    public function promotions()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Promotion', 'batches_event_id', 'id');
    }

    public function registrations()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Registration', 'batches_event_id', 'id');
    }

    public function getRegistrationsSince(Carbon $since)
    {

        return Event\Registration::where('batches_event_id', '=', $this->id)
                                ->where('created_at', '>', $since)
                                ->get();
    }

    public function getRegistrationsTodayAttribute()
    {
        return $this->getRegistrationsSince(Carbon::today());
    }

    public function getRegistrationsThisWeekAttribute()
    {
        $carbon = Carbon::today();
        $carbon->subDays($carbon->dayOfWeek);

        return $this->getRegistrationsSince($carbon);
    }

    public function getVenueAttribute()
    {
        if ($this->venue_full_address === null) {
            return null;
        }

        return (Object)[
            'name' => $this->venue_name,
            'address_1', $this->venue_address_1,
            'address_2', $this->venue_address_2,
            'city' => $this->venue_city,
            'state' => $this->venue_state,
            'postal' => $this->venue_postal,
            'country' => $this->venue_country,
            'full_address' => $this->venue_full_address
        ];
    }

    public function getVenueFullAddressAttribute()
    {
        if (!$this->venue_address_1 || !$this->venue_city || !$this->venue_state || !$this->venue_postal) {
            return null;
        }

        $address = $this->venue_address_1.', ';

        if ($this->venue_address_2) {
            $address .= $this->venue_address_2.', ';
        }

        $address .= $this->venue_city.' '.$this->venue_state.' '.$this->venue_postal;

        if ($this->venue_country) {
            $address .= ', '.$this->venue_country;
        }

        return $address;
    }

    public function getNameAttribute()
    {
        return $this->region->name;
    }

    public function getAbbrAttribute()
    {
        return $this->region->abbr;
    }

    public function getCoordinatesAttribute()
    {
        return $this->region->coordinates;
    }

    public function getWebnameAttribute()
    {
        return $this->region->webname;
    }

    public function getSimpleTimezoneAttribute()
    {
        return $this->region->simple_timezone;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = str_random(12);
        });
    }
}
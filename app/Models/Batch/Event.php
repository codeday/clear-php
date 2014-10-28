<?php
namespace CodeDay\Clear\Models\Batch;

use \Illuminate\Database\Eloquent;
use \Carbon\Carbon;

class Event extends \Eloquent {
    use Eloquent\SoftDeletingTrait;

    protected $table = 'batches_events';
    public $incrementing = false;

    public function getAllowRegistrationsCalculatedAttribute()
    {
        return $this->allow_registrations && $this->batch->allow_registrations;
    }

    public function getFullNameAttribute()
    {
        return 'CodeDay '.$this->region->name.' '.$this->batch->name;
    }

    public function getBatchNameAttribute()
    {
        return $this->batch->name;
    }

    public function getStartsAtAttribute()
    {
        return $this->batch->starts_at->timestamp;
    }

    public function getEndsAtAttribute()
    {
        return $this->batch->starts_at->addDay()->timestamp;
    }

    public function manager()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'manager_username', 'username');
    }

    public function evangelist()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'evangelist_username', 'username');
    }

    public function region()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Region', 'region_id', 'id');
    }

    public function notify()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Notify', 'batches_event_id', 'id');
    }

    public function isUserAllowed($user)
    {
        return $this->manager_username == $user->username ||
            \CodeDay\Clear\Models\User::select('users.*')
                ->join('users_grants', 'users_grants.username', '=', 'users.username')
                ->where('users_grants.username', '=', $user->username)
                ->where('users_grants.batches_event_id', '=', $this->id)
                ->exists();
    }

    public function getCostAttribute()
    {
        if ($this->is_early_bird_pricing) {
            return 10;
        } else {
            return 20;
        }
    }

    public function getIsEarlyBirdPricingAttribute()
    {
        return $this->early_bird_ends_at->isFuture()
            && $this->registrations->count() <= $this->early_bird_max_registrations;
    }

    public function getEarlyBirdEndsAtAttribute()
    {
        return $this->batch->starts_at->subWeek();
    }

    public function getStripePublicKeyAttribute()
    {
        return \Config::get('stripe.public');
    }

    public function getRemainingRegistrationsAttribute()
    {
        return max(0, $this->max_registrations - $this->registrations->count());
    }

    public function getIsEarlybirdEndingAttribute()
    {
        return $this->is_earlybird_pricing &&
        ($this->early_bird_ends_at->copy()->addDay()->isPast()
        || $this->registrations->count() >= ($this->early_bird_max_registrations - 10));
    }

    public function getEarlyBirdMaxRegistrationsAttribute()
    {
        return $this->max_registrations * 0.6;
    }

    public function getUnregisteredNotifyAttribute()
    {
        $notify = iterator_to_array($this->notify);
        $registrations = iterator_to_array($this->registrations);

        $subtraction = [];
        foreach ($notify as $entry) {
            $registered = false;

            foreach ($registrations as $reg) {
                if ($reg->email == $entry->email) {
                    $registered = true;
                }
            }

            if (!$registered) {
                $subtraction[] = $entry;
            }
        }

        return $subtraction;
    }

    public function batch()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch', 'batch_id', 'id');
    }

    public function grants()
    {
        return $this->hasMany('\CodeDay\Clear\Models\User\Grant', 'batches_event_id', 'id');
    }

    public function promotions()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Promotion', 'batches_event_id', 'id');
    }

    public function sponsors()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Sponsor', 'batches_event_id', 'id');
    }

    public function getSponsorsInfoAttribute()
    {
        $sponsors_info = [];

        foreach ($this->sponsors as $sponsor) {
            $sponsors_info[] = (object)[
                'name' => $sponsor->name,
                'logo' => 'https://clear.codeday.org/api/i/sponsor/'.$sponsor->id.'_256/'.$sponsor->updated_at->timestamp.'.jpg',
                'url' => $sponsor->description
            ];
        }

        return $sponsors_info;
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

    public function getRegistrationsAsOf(Carbon $asof)
    {

        return Event\Registration::where('batches_event_id', '=', $this->id)
            ->where('created_at', '<', $asof)
            ->get();
    }

    public function getRegistrationsOn(Carbon $date)
    {
        return Event\Registration::where('batches_event_id', '=', $this->id)
            ->where('created_at', '>', $date->copy()->subDay())
            ->where('created_at', '<', $date->copy()->addDay())
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
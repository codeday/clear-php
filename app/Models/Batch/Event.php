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
        return 'CodeDay '.$this->name.' '.$this->batch->name;
    }

    public function getBatchNameAttribute()
    {
        return $this->batch->name;
    }

    public function getStartsAtAttribute()
    {
        return Carbon::createFromTimestamp($this->batch->starts_at->timestamp, $this->region->timezone)
                    ->addHours(12)
                    ->timestamp;
    }

    public function getEndsAtAttribute()
    {
        return $this->starts_at + (60 * 60 * 24);
    }

    public function overflowFor()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'overflow_for_id', 'id');
    }

    public function overflowEvents()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'overflow_for_id', 'id');
    }

    public function getRelatedEventsAttribute()
    {
        if ($this->overflow_for_id !== null) {
            $parent_related = iterator_to_array($this->overflow_for->related_events);
            $id = $this->id;
            $parent_related = array_filter($parent_related, function($event) use ($id) {
                return $id !== $event->id;
            });

            return array_merge([$this->overflow_for], $parent_related);
        } else {
            return $this->overflow_events;
        }
    }

    /**
     * Gets the next available overflow event
     *
     * @deprecated
     * @return Event
     */
    public function getOverflowEventAttribute()
    {
        return $this->overflow_events->first();
    }

    public function prediction()
    {
        // Tom's algorithm.
        $national_events = $this->batch->events;
        $national_attendance = 0;
        foreach($national_events as $event){
            if($event->allow_registrations){
                $national_attendance += $event->registrations->count();
            }
        }
        $national_average = $national_attendance / $national_events->count();

        $regional_events = $this->batch->events;
        $regional_attendance = 0;
        foreach($regional_events as $event){
            if($event->allow_registrations && $event->region == $this->region){
                $regional_attendance += $event->registrations->count();
            }
        }
        $regional_average = $regional_attendance / $regional_events->count();

        return round(($national_average + (2 * $regional_attendance) + (3 * $national_attendance)) / 6);
    }

    public function pretty_prediction(){
        $final_prediction = $this->prediction();
        return $final_prediction >= $this->max_registrations ? $this->max_registrations . "+" : $final_prediction;
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

    public function getAttendeesHereAttribute()
    {
        return \CodeDay\Clear\Models\Batch\Event\Registration::whereNotNull('checked_in_at')
            ->where('batches_event_id', '=', $this->id)
            ->get();
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

    public function getRevenueAttribute()
    {
        $orders = \CodeDay\Clear\Models\Batch\Event\Registration
            ::where('batches_event_id', '=', $this->id)
            ->groupBy('stripe_id')
            ->get();

        $revenue = 0;
        foreach ($orders as $order) {
            $revenue += $order->order_amount_received;
        }

        return $revenue;
    }

    public function getIsEarlyBirdPricingAttribute()
    {
        return $this->early_bird_ends_at->isFuture()
            && $this->registrations->count() <= $this->early_bird_max_registrations;
    }

    public function getEarlyBirdEndsAtAttribute()
    {
        return $this->batch->starts_at->subWeek()->addDays(2);
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
        return $this->max_registrations * 0.3;
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

    public function emailsSent()
    {
        return $this->hasMany('\CodeDay\Clear\Models\EmailSent', 'batches_event_id', 'id');
    }

    public function supplies()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Supply', 'batches_event_id', 'id');
    }

    public function promotions()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Promotion', 'batches_event_id', 'id');
    }

    public function sponsors()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Sponsor', 'batches_event_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Activity', 'batches_event_id', 'id')->orderBy('time');
    }

    public function getScheduleAttribute()
    {
        $standard_schedule = [
            (Object)[
                'time' => -1,
                'title' => 'Doors open',
                'type' => 'event',
                'description' => "Please don't show up earlier, you'll be waiting outside!"
            ],
            (Object)[
                'time' => -0.5,
                'title' => 'Lunch',
                'type' => 'event',
                'description' => "All food is included with your ticket!"
            ],
            (Object)[
                'time' => 0,
                'title' => 'Kickoff & Pitches',
                'type' => 'event',
                'description' => "Not sure what you want to work on? Get some ideas and form a team at the kickoff."
            ],
            (Object)[
                'time' => 1,
                'title' => 'Start Coding!',
                'type' => 'event',
                'description' => "After forming teams, it's time to get to work on your project! Our staff will be helping teams throughout the event."
            ],
            (Object)[
                'time' => 7,
                'title' => 'Dinner',
                'type' => 'event',
                'description' => "All food is included with your ticket!"
            ],
            (Object)[
                'time' => 12,
                'title' => 'Midnight Snack',
                'type' => 'event',
                'description' => "All food is included with your ticket!"
            ],
            (Object)[
                'time' => 19,
                'title' => 'Breakfast',
                'type' => 'event',
                'description' => "All food is included with your ticket!"
            ],
            (Object)[
                'time' => 20,
                'title' => 'Presentation sign-up',
                'type' => 'event',
                'description' => "The final few hours. Teams with something to show are encouraged to sign up to present at the end."
            ],
            (Object)[
                'time' => 21,
                'title' => 'Judges arrive',
                'type' => 'event',
                'description' => "Judges will go around to demo the projects before the final presentations."
            ],
            (Object)[
                'time' => 22,
                'title' => 'Presentations',
                'type' => 'event',
                'description' => "Everyone who created something during CodeDay is asked to give a brief presentation."
            ],
            (Object)[
                'time' => 23.5,
                'title' => 'Awards',
                'type' => 'event',
                'description' => "Awards given for Top Overall, Best App, Best Game, and more."
            ],
            (Object)[
                'time' => 24,
                'title' => 'Clean-up',
                'type' => 'event',
                'description' => "Thank the venue for hosting CodeDay by helping clean up!"
            ]
        ];

        $workshops = [
            (Object)[
                'time' => 2,
                'title' => 'Beginner: Intro to coding',
                'type' => 'workshop',
                'url' => 'https://www.scirra.com/tutorials/37/beginners-guide-to-construct-2',
                'description' => "Totally new to coding? No problem! Attend this workshop and we'll walk you through creating your first game."
            ],
            (Object)[
                'time' => 5,
                'title' => 'Splunk: Make your game better with data',
                'type' => 'workshop',
                'url' => 'https://studentrnd.org/build/making-better-games-with-splunk',
                'description' => 'Learn how others play your game and use that knowledge to make it more fun with minimal work.'
            ]
        ];

        if (!$this->hide_default_workshops) {
            $standard_schedule = array_merge($standard_schedule, $workshops);
        }

        // Add timestamy/hour/day to generated array
        for ($i = 0; $i < count($standard_schedule); $i++) {
            $entry = $standard_schedule[$i];

            $timestamp = $this->batch->starts_at->copy()->addHours(12)->addMinutes($entry->time * 60);
            $day = $timestamp->format('l');
            if (floor($this->time) != $this->time) {
                $hour = $timestamp->format('g:ia');
            } else {
                $hour = $timestamp->format('ga');
            }

            $standard_schedule[$i]->timestamp = $timestamp;
            $standard_schedule[$i]->hour = $hour;
            $standard_schedule[$i]->day = $day;
        }

        // Build the unified schedule
        $unified_schedule = array_merge($standard_schedule, iterator_to_array($this->activities));
        usort($unified_schedule, function($a, $b) {
            return ($a->time - $b->time) * 100;
        });

        // Make sure getters are gotten
        for ($i = 0; $i < count($unified_schedule); $i++) {
            $unified_schedule[$i]->timestamp = $unified_schedule[$i]->timestamp;
            $unified_schedule[$i]->hour = $unified_schedule[$i]->hour;
            $unified_schedule[$i]->day = $unified_schedule[$i]->day;
        }

        // Sort by date
        $days = ['Saturday' => [], 'Sunday' => []];
        foreach ($unified_schedule as $entry) {
            $days[$entry->day][] = $entry;
        }

        return $days;
    }

    public function getManifestGeneratedAttribute()
    {
        $items = [];

        // Get total attendance information (for distributing inventory)
        $total_events = 0;
        $total_attendees = 0;
        foreach ($this->batch->events as $event) {
            if (!$event->ship_for) {
                throw new \Exception('No ship for estimate for '.$event->name);
            }

            $total_events++;
            $total_attendees += $event->ship_for;
        }

        // Add batch items
        foreach ($this->batch->supplies as $supply) {
            $a_quantity = 0;
            if ($supply->type == 'perbox') {
                $a_quantity = $supply->quantity;
            } elseif ($supply->type == 'perparticipant') {
                $a_quantity = round($supply->quantity * $this->ship_for);
            } elseif ($supply->type == 'inventory') {
                $a_quantity = floor(($this->ship_for/$total_attendees) * $supply->quantity);
            }

            $items[] = [
                'item' => $supply->item,
                'quantity' => $a_quantity
            ];
        }

        // Add event items
        foreach ($this->supplies as $supply) {
            $a_quantity = 0;
            if ($supply->type == 'perbox') {
                $a_quantity = $supply->quantity;
            } elseif ($supply->type == 'perparticipant') {
                $a_quantity = round($supply->quantity * $this->ship_for);
            }

            $items[] = [
                'item' => $supply->item,
                'quantity' => $a_quantity
            ];
        }

        return $items;
    }

    public function getEmergencyPhoneAttribute()
    {
        if ($this->evangelist && $this->evangelist->phone) {
            return $this->evangelist->phone;
        } else {
            return '12068538786';
        }
    }

    public function getShipReadyAttribute()
    {
        return
            ($this->ship_address
                && $this->ship_for
                && $this->ship_l && $this->ship_w && $this->ship_h
                && $this->ship_weight
                && $this->shipment_number == null);
    }

    public function getShipAddressAttribute()
    {
        return
            ($this->ship_address_1 && $this->ship_city && $this->ship_state && $this->ship_postal
                && ($this->ship_name || $this->ship_company));
    }

    public function getSponsorsInfoAttribute()
    {
        $sponsors_info = [];

        foreach ($this->sponsors as $sponsor) {
            $sponsors_info[] = (object)[
                'name' => $sponsor->name,
                'logo' => 'https://clear.codeday.org/api/i/sponsor/'.$sponsor->id.'_512/'.$sponsor->updated_at->timestamp.'.jpg',
                'url' => $sponsor->url
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

    public function registrationsSortedBy($column)
    {
      return Event\Registration::where('batches_event_id', '=', $this->id)
        ->orderBy($column, 'asc')
        ->get();
    }

    public function getRegistrationsSortedByFirstNameAttribute()
    {
        return Event\Registration::where('batches_event_id', '=', $this->id)
          ->orderBy('first_name', 'asc')
          ->get();
    }

    public function getRegistrationsSortedByLastNameAttribute()
    {
        return Event\Registration::where('batches_event_id', '=', $this->id)
          ->orderBy('last_name', 'asc')
          ->get();
    }

    public function getVenueAttribute()
    {
        if ($this->venue_full_address === null) {
            return null;
        }

        return (Object)[
            'name' => $this->venue_name,
            'address_1' => $this->venue_address_1,
            'address_2' => $this->venue_address_2,
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
        if ($this->name_override) {
            return $this->name_override;
        } else {
            return $this->region->name;
        }
    }

    public function getAbbrAttribute()
    {
        if ($this->abbr_override) {
            return $this->abbr_override;
        } else {
            return $this->region->abbr;
        }
    }

    public function getCoordinatesAttribute()
    {
        return $this->region->coordinates;
    }

    public function getWebnameAttribute()
    {
        if ($this->webname_override) {
            return $this->webname_override;
        } else {
            return $this->region->webname;
        }
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

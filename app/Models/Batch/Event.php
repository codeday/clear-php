<?php
namespace CodeDay\Clear\Models\Batch;

use \Illuminate\Database\Eloquent;
use \CodeDay\Clear\Models;
use \Carbon\Carbon;

/**
 * Class Event
 * @package CodeDay\Clear\Models\Batch
 *
 * @property Models\SigningRequest  $signing_request
 * @property string                 $agreement_signed_url;
 */
class Event extends \Eloquent {
    public function getAllowRegistrationsCalculatedAttribute()
    {
        return $this->allow_registrations && $this->batch->allow_registrations;
    }

    public function getSignatureAttribute()
    {
        return hash_hmac('sha256', \Config::get('app.key'), $this->id);
    }

    public function getFullNameAttribute()
    {
        return 'CodeDay '.$this->name.' '.$this->batch->name;
    }

    public function getBatchNameAttribute()
    {
        return $this->batch->name;
    }

    public function getPreviousEventAttribute()
    {

        return Models\Batch\Event
                ::select('batches_events.*')
                ->join('batches', 'batches_events.batch_id', '=', 'batches.id')
                ->where('batches.starts_at', '<', $this->batch->starts_at)
                ->where('batches_events.region_id', '=', $this->region_id)
                ->where('batches_events.allow_registrations', '=', true)
                ->orderBy('batches.starts_at', 'DESC')
                ->first();
    }

    //Calculates the average age of an attendee as long as they're within the 'scope'
    public function getAvgAgeAttribute()
    {
        $age = 0;
        $count = 0;

        foreach($this->registrations as $reg){
            if($reg->age > 5 && $reg->age < 30){
                $age += $reg->age;
                $count++;
            }
        }

        if($count !== 0){
          $age = $age / $count;
        }else{
          $age = 0;
        }

        return $age;

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

    public function getCurrencyAttribute()
    {
        $specialCurrencies = [
            'ca' => 'cad'
        ];
        return $specialCurrencies[strtolower($this->venue_country)] ?? 'usd';
    }

    public function getAttendeesRequestingLoanersAttribute()
    {
        return \CodeDay\Clear\Models\Batch\Event\Registration::where('request_loaner', '=', true)
            ->where('batches_event_id', '=', $this->id)
            ->get();
    }

    public function getLoanersClaimedAttribute()
    {
        return \CodeDay\Clear\Models\Batch\Event\Registration::where('request_loaner', '=', true)
            ->where('batches_event_id', '=', $this->id)
            ->count();
    }

    public function getLoanersUnclaimedAttribute()
    {
        return max($this->loaners_available - $this->loaners_claimed, 0);
    }

    public function getPreviousAttribute()
    {
        if (!$this->batch->previous) {
            return null;
        }

        return self
            ::where('region_id', '=', $this->region_id)
            ->where('batch_id', '=', $this->batch->previous->id)
            ->first();
    }

    protected $_overflowFor = null;
    public function getOverflowForAttribute()
    {
        if (!isset($this->_overflowFor)) {
            $this->_overflowFor = self::where('id', '=', $this->overflow_for_id)->first();
        }

        return $this->_overflowFor;
    }

    public function overflowEvents()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'overflow_for_id', 'id');
    }

    public function getRelatedEventsAttribute()
    {
        if ($this->overflow_for_id !== null) {
            $parent_related = iterator_to_array($this->overflowFor->relatedEvents);
            $id = $this->id;
            $parent_related = array_filter($parent_related, function($event) use ($id) {
                return $id !== $event->id;
            });

            return array_merge([$this->overflowFor], $parent_related);
        } else {
            return $this->overflowEvents;
        }
    }

    public function getShipByAttribute()
    {
        $shipDate = $this->batch->starts_at->addDays(-1);
        for ($i = 0; $i < $this->region->ground_days_in_transit; $i++) {
            $shipDate->addDays(-1);
            while (in_array($shipDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $shipDate->addDays(-1);
            }
        }

        return $shipDate;
    }

    /**
     * Gets the next available overflow event
     *
     * @deprecated
     * @return Event
     */
    public function getOverflowEventAttribute()
    {
        return $this->overflowEvents->first();
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

    public function getSalesTaxRateAttribute() {
        if (!$this->venue_state || !$this->venue_country || !$this->venue_postal) return null;
        try {
            return \Cache::remember('event_tax_rate_'.$this->id.'_'.$this->venue_postal, 60*60*24, function() {
                $c = \TaxJar\Client::withApiKey(\Config::get('taxjar.token'));
                $taxRate = $c->taxForOrder([
                    'to_country' => $this->venue_country,
                    'to_state' => $this->venue_state,
                    'to_city' => $this->venue_city,
                    'to_zip' => $this->venue_postal,
                    'shipping' => 0,
                    'line_items' => [
                        [
                            'id' => 1,
                            'quantity' => 1,
                            'product_tax_code' => \Config::get('taxjar.category'),
                            'unit_price' => $this->price_regular
                        ]
                    ]
                ]);
                return $taxRate->rate ?? 0;
            });
        } catch (\Exception $ex) {
            return 0;
        }

    }

    public function pretty_prediction(){
        $final_prediction = $this->prediction();
        return $final_prediction >= $this->max_registrations ? $this->max_registrations . "+" : $final_prediction;
    }

    public function manager()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'manager_username', 'username');
    }

    public function coach()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'coach_username', 'username');
    }

    public function evangelist()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'evangelist_username', 'username');
    }

    public function region()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Region', 'region_id', 'id');
    }

    private static $_regions;
    public function getRegionAttribute()
    {
        if (!isset(self::$_regions)) {
            self::$_regions = Models\Region::get();
        }

        $regionId = $this->region_id;
        return self::$_regions->filter(function($x) use ($regionId) { return $x->id == $regionId; })->values()[0];
    }

    public function specialLinks()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\SpecialLink', 'batches_event_id', 'id');
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
            return $this->price_earlybird;
        } else {
            return $this->price_regular;
        }
    }

    public function getTicketRevenueAttribute()
    {
        $amount = \CodeDay\Clear\Models\Batch\Event\Registration
            ::where('batches_event_id', '=', $this->id)
            ->sum('amount_paid');

        $count = \CodeDay\Clear\Models\Batch\Event\Registration
            ::selectRaw('COUNT(DISTINCT stripe_id) as count')
            ->where('batches_event_id', '=', $this->id)
            ->first()->count;

        return ($amount * (1-0.027)) - ($count * 0.30);
    }

    public function getSponsorRevenueAttribute()
    {
        $summary = \CodeDay\Clear\Models\Batch\Event\Sponsor
            ::selectRaw('SUM(amount) as amount')
            ->where('batches_event_id', '=', $this->id)
            ->first();

        return $summary->amount;
    }

    public function getRevenueAttribute()
    {
        return $this->ticket_revenue + $this->sponsor_revenue;
    }

    public function getContractCostsAttribute()
    {
        if ($this->sposnor_revenue > 1000) {
            return 750;
        } else {
            return 0;
        }
    }

    public function getFoodCostsAttribute()
    {
        return count($this->registrations)*10; // TODO
    }

    public function getCostsAttribute()
    {
        return $this->contract_costs + $this->food_costs;
    }

    public function getIsEarlyBirdPricingAttribute()
    {
        return $this->early_bird_ends_at->isFuture()
            && $this->registrations->count() <= $this->early_bird_max_registrations
            && $this->price_earlybird < $this->price_regular;
    }

    public function getEarlyBirdEndsAtAttribute()
    {
        return $this->batch->starts_at->subWeek()->addDays(4);
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
        return floor($this->max_registrations * 0.3);
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

    public function getBatchAttribute()
    {
        $batchId = $this->batch_id;
        return \Cache::remember('batch_'.$this->batch_id, 30, function() use ($batchId) {
            return Models\Batch::where('id', '=', $batchId)->firstOrFail();
        });
    }

    public function grants()
    {
        return $this->hasMany('\CodeDay\Clear\Models\User\Grant', 'batches_event_id', 'id');
    }

    public function getSubusersAttribute()
    {
        return array_map(function($x) {
            return $x->user;
        }, iterator_to_array($this->grants));
    }

    public function announcements()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\Announcement', 'batches_event_id', 'id');
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

    public function flights()
    {
        return $this
            ->hasMany('\CodeDay\Clear\Models\Batch\Event\Flight', 'batches_event_id', 'id')
            ->orderBy('departs_at');
    }

    public function getFlightPlansAttribute()
    {
        $travelers = [];
        foreach (Event\Flight::groupBy('traveler_username')->where('batches_event_id', '=', $this->id)->get()
                 as $flight) {
            $travelers[] = $flight->traveler;
        }

        $plans = [];
        foreach ($travelers as $traveler) {
            $plan = (object)['traveler' => $traveler, 'to' => [], 'from' => []];
            foreach (Event\Flight
                         ::orderBy('departs_at')
                         ->where('batches_event_id', '=', $this->id)
                         ->where('traveler_username', '=', $traveler->username)
                         ->get()
                     as $flight) {
                $plan->{$flight->direction}[] = $flight;
            }
            $plans[] = $plan;
        }

        return $plans;
    }

    public function getScheduleAttribute($includeInternal = false)
    {
        $standard_schedule = [
            (Object)[
                'time' => -1,
                'title' => 'Doors open',
                'type' => 'event',
                'description' => "Please don't show up earlier, you'll be waiting outside!"
            ],
            (Object)[
                'time' => 0,
                'title' => 'Kickoff & Pitches',
                'type' => 'event',
                'description' => "Not sure what you want to work on? Our Code Evangelists will help you get some ideas and form a team."
            ],
            (Object)[
                'time' => 0.5,
                'title' => 'Start Coding!',
                'type' => 'event',
                'description' => "After forming teams, it's time to get to work on your project! Our Code Evangelists and other mentors will be helping teams throughout the event."
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
                'time' => 0.5,
                'title' => 'Beginner: Intro to coding',
                'type' => 'workshop',
                'url' => 'https://blog.srnd.org/jumping-over-chairs-and-learning-to-make-a-game-with-construct-2-a796f4def9e9#.v37wzagti',
                'description' => "Totally new to coding? No problem! Attend this workshop and our Code Evangelists will walk you through creating your first game."
            ],
            (Object)[
                'time' => 7 + ($this->getTimezoneOffset($this->region->timezone) - $this->getTimezoneOffset('America/Los_Angeles')),
                'title' => 'CodeCup (presented by Splunk)',
                'type' => 'workshop',
                'url' => 'https://playcodecup.com/',
                'description' => 'Work with other attendees to make your city #1 worldwide!'
            ],
            (Object)[
                'time' => 9 + ($this->getTimezoneOffset($this->region->timezone) - $this->getTimezoneOffset('America/Los_Angeles')),
                'title' => 'Global Game Tournament',
                'type' => 'workshop',
                'url' => 'https://www.srnd.org/codeday-gaming-tourney',
                'description' => 'Compete against other CodeDay participants. The top three win a Kinesis Gaming Keyboard!'
            ],
        ];

        $meals = [
            (Object)[
                'time' => -0.5,
                'title' => 'Lunch',
                'type' => 'event',
                'description' => "All food is included with your ticket!"
            ],
            (Object)[
                'time' => 6,
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
            ]
        ];

        $internal = [
            (Object)[
                'time' => -0.25,
                'title' => 'Icebreaker',
                'type' => 'internal',
                'description' => 'Lead Code Evangelist to lead kickoff icebreaker, see Evg guide.'
            ],
            (Object)[
                'time' => 0.25,
                'title' => 'Sponsors',
                'type' => 'internal',
                'description' => ''
            ],
            (Object)[
                'time' => 0.5,
                'title' => 'Pitches',
                'type' => 'internal',
                'description' => 'All volunteers and staff pitch silly ideas to get things started.'
            ],
            (Object)[
                'time' => 2,
                'title' => 'Natnl mentor shifts end',
                'type' => 'internal',
                'description' => 'SRND-national scheduled mentors only.'
            ],
            (Object)[
                'time' => 2.5,
                'title' => 'Add teams to Showcase',
                'type' => 'internal',
                'description' => 'Volunteer should add each team to showcase.srnd.org.'
            ],
            (Object)[
                'time' => 11,
                'title' => 'Natnl mentor shifts end',
                'type' => 'internal',
                'description' => 'SRND-national scheduled mentors only.'
            ],
        ];

        if (!$this->hide_default_workshops) {
            $standard_schedule = array_merge($standard_schedule, $workshops);
        }

        if (!$this->hide_meals){
            $standard_schedule = array_merge($standard_schedule, $meals);
        }

        if ($includeInternal) {
            $standard_schedule = array_merge($standard_schedule, $internal);
        }

        // Add timestamp/hour/day to generated array
        for ($i = 0; $i < count($standard_schedule); $i++) {
            $entry = $standard_schedule[$i];

            $timestamp = $this->batch->starts_at->copy()->addHours(12)->addMinutes($entry->time * 60);
            $day = $timestamp->format('l, F j');
            $hour = $timestamp->format('g:ia');

            $standard_schedule[$i]->timestamp = $timestamp;
            $standard_schedule[$i]->hour = $hour;
            $standard_schedule[$i]->day = $day;
        }

        // Get activities
        $activities = [];
        foreach ($this->activities as $activity) {
            if ($activity->type == 'internal' && !$includeInternal) continue;
            $activity->timestamp->tz = $this->region->timezone;
            $activities[] = (object)[
                'time' => floatval($activity->time),
                'title' => $activity->title,
                'type' => $activity->type,
                'url' => $activity->url,
                'description' => $activity->description,
                'timestamp' => $activity->timestamp,
                'hour' => $activity->hour,
                'day' => date('l, F j', ($activity->day == 'Saturday' ? $this->starts_at : $this->ends_at))
            ]; // HACK
        }

        // Build the unified schedule
        $unified_schedule = array_merge($standard_schedule, $activities);
        usort($unified_schedule, function($a, $b) {
            return ($a->time - $b->time) * 100;
        });

        // Make sure getters are gotten
        for ($i = 0; $i < count($unified_schedule); $i++) {
            $offset = $unified_schedule[$i]->timestamp->offset;
            $unified_schedule[$i]->timestamp->timezone = $this->region->timezone;
            $unified_schedule[$i]->timestamp->addSeconds($offset - $unified_schedule[$i]->timestamp->offset);
            $unified_schedule[$i]->hour = $unified_schedule[$i]->hour;
            $unified_schedule[$i]->day = $unified_schedule[$i]->day;
        }

        // Sort by date
        $days = [];
        foreach ($unified_schedule as $entry) {
            $days[$entry->day][] = $entry;
        }

        return $days;
    }

    private function getTimezoneOffset($region) {
        $x = timezone_offset_get(new \DateTimeZone($region), Carbon::createFromTimestamp($this->starts_at, $region))/(60*60);
        if ($this->region_id === 'phoenix' || $this->region_id === 'arizona') $x -= 1;
        return $x;
    }

    public function getManifestGeneratedAttribute()
    {
        if (count($this->batch->supplies) == 0) {
            return [[
                'sku' => 'SUP-S',
                'name' => 'Seasonal Event Supplies',
                'quantity' => 1,
                'weight' => ['value' => 2, 'unit' => 'lbs']
            ]];
        } else {
            $items = [];
            // Get total attendance information (for distributing inventory)
            $total_events = 0;
            $total_attendees = 0;
            foreach ($this->batch->events as $event) {
                if (!$event->ship_for) {
                    throw new \Exception('No ship for estimate for '.$event->name);
                    $event->ship_for = 100;
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
                    'sku' => $supply->sku,
                    'name' => $supply->item,
                    'quantity' => intval($a_quantity),
                    'weight' => ['value' => floatval($supply->weight), 'units' => 'pounds']
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
                    'sku' => $supply->sku,
                    'name' => $supply->item,
                    'quantity' => intval($a_quantity),
                    'weight' => ['value' => floatval($supply->weight), 'units' => 'pounds']
                ];
            }
            return $items;
        }
    }

    public function getEmergencyPhoneAttribute()
    {
        if ($this->evangelist && $this->evangelist->phone) {
            return $this->evangelist->phone;
        } else {
            return '1000@sip.studentrnd.org:5080';
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

    public function supportCalls()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event\SupportCall', 'batches_event_id', 'id')->orderBy('created_at', 'DESC');
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

    public function registrationsSortedBy($column, $order = 'desc')
    {
        $q = Event\Registration::where('batches_event_id', '=', $this->id)
            ->with("devices");

        if ($column === "type")
            $q->orderByRaw('FIELD(type, "student") ASC')->orderBy("type");
        else
          $q->orderBy($column, $order);

        if ($column !== "created_at") $q->orderBy("created_at", "asc");

        return $q->get();
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

    public function getAnnouncementsAttribute()
    {
        return Models\Batch\Event\Announcement::where("batches_event_id", "=", $this->id)->orderBy("created_at", "desc")->get();
    }

    // ## Contracts

    public function agreement()
    {
        return $this->hasOne('\CodeDay\Clear\Models\Agreement', 'id', 'agreement_id');
    }

    /**
     * Gets the signing request
     *
     * @return Models\SigningRequest
     * @throws \Exception
     */
    public function getSigningRequestAttribute()
    {
        if (!isset($this->agreement_signing_id)) {
            if (!isset($this->agreement_id)) {
                throw new \Exception('No agreement to sign.');
            }

            $signingRequest = Models\SigningRequest::NewFromHtml(
                $this->agreement->name,
                $this->agreement->RenderHtmlFor($this),
                (object)[
                    'firstName' => $this->manager->first_name,
                    'lastName' => $this->manager->last_name,
                    'email' => $this->manager->email
                ]
            );
            $this->agreement_signing_id = $signingRequest->id;
            $this->save();
        }

        return Models\SigningRequest::FromId($this->agreement_signing_id);
    }

    public function getAgreementPdfAttribute()
    {
        if (!isset($this->agreement_signed_url)) {
            if (!isset($this->agreement_signing_id) || !$this->signing_request->HasPdf()) {
                return null;
            }

            $this->agreement_signed_url = $this->signing_request->GetMirroredPdf();
            $this->save();
        }

        return $this->agreement_signed_url;
    }

    // # Laravel

    use Eloquent\SoftDeletes;
    protected $table = 'batches_events';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = str_random(12);
            $model->price_earlybird = config('app.price_earlybird');
            $model->price_regular = config('app.price_regular');
        });
    }
}

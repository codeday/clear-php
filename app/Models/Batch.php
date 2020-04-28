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

    public function getPreviousAttribute()
    {
        return self
            ::where('starts_at', '<', $this->starts_at)
            ->orderBy('starts_at', 'DESC')
            ->first();
    }

    public function getRegistrationsAttribute()
    {
        return Batch\Event\Registration
            ::select('batches_events_registrations.*')
            ->join('batches_events', 'batches_events_registrations.batches_event_id', '=', 'batches_events.id')
            ->where('batches_events.batch_id', '=', $this->id)
            ->get();
    }

    public function EventWithWebname(string $webname) : Batch\Event
    {
        return Batch\Event
            ::where('batch_id', '=', $this->id)
            ->where(function($group) use ($webname) {
                return $group
                    ->where('webname_override', '=', $webname)
                    ->orWhere(function($w2) use ($webname) {
                        return $w2
                            ->where('region_id', '=', $webname)
                            ->whereNull('webname_override');
                    });
            })
            ->orderBy('webname_override')
            ->firstOrFail();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = str_random(12);
        });
    }

    private static $_loaded = null;
    public static function Loaded()
    {
        if (!isset(self::$_loaded)) {
            self::$_loaded = \Cache::remember('loaded', \config('app.debug') ? 0 : 15, function(){
                $batch = Batch::where('is_loaded', '=', true)->first();
                if (!$batch) {
                    $batch = Batch::orderBy('starts_at', 'DESC')->firstOrFail();
                    $batch->is_loaded = true;
                    $batch->save();
                }

                return $batch;
            });
        }

        return self::$_loaded;
    }

    private static $_loadedAll = null;
    public static function LoadedAll()
    {
        if (!isset(self::$_loadedAll)) {
            self::$_loadedAll = \Cache::remember('loaded_all', \config('app.debug') ? 0 : 15, function(){
                $batches = Batch::where('is_loaded', '=', true)->orderBy('starts_at', 'ASC')->get();
                if (count($batches) == 0) {
                    $batch = Batch::orderBy('starts_at', 'DESC')->firstOrFail();
                    $batch->is_loaded = true;
                    $batch->save();
                    $batches = [$batch];
                }

                return $batches;
            });
        }

        return self::$_loadedAll;
    }

    private static $_managed = null;
    public static function Managed()
    {
        if (!isset(self::$_managed)) {
            if (\Session::get('managed_batch_id')) {
                $batch = self::where('id', '=', \Session::get('managed_batch_id'))->with('events')->first();
                if (!isset($batch) || (count(User::me()->getManagedEvents($batch)) == 0 && !User::me()->is_admin)) $batch = null;
            }

            if (!isset($batch)) {
                foreach (self::orderBy('starts_at', 'DESC')->get() as $recentBatch) {
                    if (User::is_logged_in() && (count(User::me()->getManagedEvents($recentBatch)) > 0 || User::me()->is_admin)) {
                        $batch = $recentBatch;
                        break;
                    }
                }

                if (!isset($batch)) \abort(401);

                \Session::set('managed_batch_id', $batch);
            }

            self::$_managed = $batch;
            \View::share('managed_batch', $batch); // HACK
        }

        if(self::$_managed == null) self::$_managed = self::Loaded();
        return self::$_managed;
    }

    public function manage()
    {
        self::$_managed = $this;
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

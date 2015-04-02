<?php
namespace CodeDay\Clear\Models;

use Illuminate\Database\Eloquent;

class Region extends \Eloquent {
    use Eloquent\SoftDeletingTrait;

    public $_webname; // hack
    public $_event_override;
    protected $table = 'regions';

    public function events()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'region_id', 'id');
    }

    public function getAllEventsAttribute()
    {
        return Batch\Event
            ::select('batches_events.*')
            ->where('region_id', '=', $this->id)
            ->leftJoin('batches', 'batches.id', '=', 'batches_events.batch_id')
            ->orderBy('batches.starts_at', 'DESC')
            ->get();
    }

    public function getCoordinatesAttribute()
    {
        return (object)[
            'lat' => $this->lat,
            'lng' => $this->lng
        ];
    }

    public function getSimpleTimezoneAttribute()
    {
        switch ($this->timezone) {
            case 'America/Los_Angeles':
                return 'Pacific';
            case 'America/Denver':
                return 'Mountain';
            case 'America/Chicago':
                return 'Central';
            case 'America/Detroit':
                return 'Eastern';
            default:
                return $this->timezone;
        }
    }

    public function getWebnameAttribute()
    {
        if ($this->_webname) {
            return $this->_webname;
        } else {
            return $this->id;
        }
    }

    public function getCurrentEventAttribute()
    {
        if ($this->_event_override) {
            return $this->_event_override;
        } else {
            return Batch\Event::where('region_id', '=', $this->id)
                ->where('batch_id', '=', Batch::Loaded()->id)
                ->whereNull('overflow_for_id')
                ->first();
        }
    }
}
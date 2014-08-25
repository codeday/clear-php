<?php
namespace CodeDay\Clear\Models;

use Illuminate\Database\Eloquent;

class Region extends \Eloquent {
    use Eloquent\SoftDeletingTrait;

    protected $table = 'regions';

    public function events()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'region_id', 'id');
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
        return $this->id;
    }

    public function getCurrentEventAttribute()
    {
        return Batch\Event::where('region_id', '=', $this->id)
            ->where('batch_id', '=', Batch::Loaded()->id)
            ->first();
    }
}
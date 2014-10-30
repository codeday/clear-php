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
        return ['created_at', 'updated_at', 'starts_at'];
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

    public function has_region(Region $r) {
        foreach ($this->events as $event) {
            if ($event->region_id === $r->id) {
                return true;
            }
        }

        return false;
    }
} 
<?php
namespace CodeDay\Clear\Models\Batch\Event;

use Illuminate\Database\Eloquent\SoftDeletes;
use CodeDay\Clear\Services;
use CodeDay\Clear\Jobs;

class Announcement extends \Eloquent {
    protected $table = 'batches_events_announcements';
    public $incrementing = false;

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'creator_username', 'username');
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

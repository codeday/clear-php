<?php
namespace CodeDay\Clear\Models\Batch\Event;

use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Call extends \Eloquent {
    protected $table = 'batches_events_calls';
    const ExternalNumber = '8882633230';
    const ExternalNumberPhonetic = 'eight eight eight. code, two thirty';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'creator_username', 'username');
    }

    public function getFullTranscriptAttribute()
    {
        return
            "This is an automated call from ".$this->creator->first_name." from Code Day ".$this->event->name.". "
            .str_replace(
                [
                    'codeday',
                    ','
                ],
                [
                    'code day',
                    '.'
                ],
                strtolower($this->transcript)
            )
            ." Again, this was an automated call from Code Day. If you have any questions, please call this number back"
            ." and you will be connected to a person. You can call ".self::ExternalNumberPhonetic."."
            ." This message will be repeated three times. . . . .";
    }
}
<?php

namespace CodeDay\Clear\Jobs;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use CodeDay\Clear\Models;
use CodeDay\Clear\Models\Batch\Event;
use CodeDay\Clear\Services;

class SyncProfileImageJob extends Job
{
    use DispatchesJobs, SerializesModels;
    protected $reg;

    public function __construct(Event\Registration $reg)
    {
        $this->reg = $reg;
    }

    public function handle()
    {
        return;
        $fc_response = Services\FullContact::getDataFor($this->reg);

        if ($fc_response === false) {     // Data crawl in progress
            $s = new self($this->reg);
            $s->delay(60*60*6);
            $this->dispatch($s);
            return;
        }
        if (!isset($fc_response) || !isset($fc_response->photos)) return; // No data

        if ($fc_response->likelihood < 0.6) return;

        $primary_photo = null;
        foreach ($fc_response->photos as $photo) {
            if (isset($photo->isPrimary) && $photo->isPrimary) $primary_photo = $photo->url;
        }

        if (!isset($primary_photo) && count($fc_response->photos) > 0) $primary_photo = $fc_response->photos[0]->url;

        if (isset($primary_photo)) {
            $this->reg->profile_image = $primary_photo;
            $this->reg->save();
        }
    }
}

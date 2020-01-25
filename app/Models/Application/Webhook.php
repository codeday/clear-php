<?php
namespace CodeDay\Clear\Models\Application;

use Illuminate\Database\Eloquent;
use \CodeDay\Clear\Services;

class Webhook extends \Eloquent {
    protected $table = 'applications_webhooks';

    public function application()
    {
      return $this->belongsTo('\CodeDay\Clear\Models\Application', 'application_id', 'public');
    }

    public function Execute($data)
    {
        $opts = ['http' => ['method'  => 'POST']];
        $body = [];

        $body['data'] = $data;

        $body['private'] = $this->application->private;
        $body['event'] = $this->event;

        $opts['http']['header'] = 'Content-type: application/json';
        $opts['http']['content'] = json_encode($body);

        $url = $this->url;
        try {
          \Queue::push(function ($job) use ($opts, $url) {
            $context  = stream_context_create($opts);
            @file_get_contents($url, false, $context);
            $job->delete();
          });
        } catch (\Exception $ex) {}
    }

    public static function Fire($event, $data)
    {
      $all_hooks = self::where('event', '=', $event)->get();
      foreach ($all_hooks as $hook) {
        $hook->Execute($data);
      }
    }

    protected static function boot()
    {
      parent::boot();

      static::creating(function ($model) {
        $model->id  = str_random(16);
      });
    }
}

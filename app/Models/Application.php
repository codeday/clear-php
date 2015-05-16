<?php
namespace CodeDay\Clear\Models;

use \Carbon\Carbon;

class Application extends \Eloquent {
    protected $table = 'applications';
    protected $primaryKey = 'public';
    public $incrementing = false;

    public function admin()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'admin_username', 'username');
    }

    public function webhooks()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Application\Webhook', 'application_id', 'public');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->public  = str_random(32);
            $model->private = str_random(32);
        });
    }
}

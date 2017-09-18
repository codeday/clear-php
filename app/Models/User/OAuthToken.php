<?php
namespace CodeDay\Clear\Models\User;

use Illuminate\Database\Eloquent;

class OAuthToken extends \Eloquent {
    protected $table = 'users_oauth_tokens';
    protected $primaryKey = 'access_token';

    protected static $scopeMap = [
        "events" => "View your events",
        "events.registrations" => "Manage registrations for your events",
        "events.announcements" => "Manage announcements for your events",
        "events.subscriptions" => "View your events' mailing lists",
        "events.email" => "Send email on your behalf",
        "events.promotions" => "Manage promotions for your events",
        "events.venue" => "Manage venue info for your events",
        "events.sponsors" => "Manage sponsors for your events",
        "events.activities" => "Schedule activities for your events",
        "events.subusers" => "Invite other users to manage your events",
        "events.shipping" => "Manage shipping info for your events",
        "events.preevent" => "Manage the pre-event email for your events"
    ];

    public function application()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Application', 'application_token', 'public');
    }

    public function user()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'user_username', 'username');
    }

    public function getScopeArrayAttribute()
    {
        return explode(" ", $this->scopes);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->token = str_random(64);
            $model->access_token = str_random(64);
            $model->access_token_used = false;
        });
    }

    public static function validScopes($scopes = [ ]) {
        $validScopes = [ ];

        foreach($scopes as $scope) {
            if(array_key_exists(trim($scope), self::$scopeMap)) {
                $validScopes[] = trim($scope);
            }
        }

        return $validScopes;
    }

    public static function humanReadableScopes($scopes = [ ]) {
        $humanScopes = [ ];

        foreach($scopes as $scope) {
            if(array_key_exists($scope, self::$scopeMap)) {
                $humanScopes[] = self::$scopeMap[$scope];
            }
        }

        return $humanScopes;
    }
}
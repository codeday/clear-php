<?php
namespace CodeDay\Clear\Models;

use \CodeDay\Clear\Services;
use \Carbon\Carbon;

class User extends \Eloquent {
    protected $table = 'users';
    protected $primaryKey = 'username';
    public $incrementing = false;
    public $wasFirstLogin = false;

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'logged_in_at'];
    }


    public function check_group($group)
    {
        if ($this->is_admin) {
            return true;
        }

        foreach ($this->groups as $m_group) {
            if ($group == $m_group->id) {
                return true;
            }
        }

        return false;
    }

    public function check_groups($groups)
    {
        foreach ($groups as $group) {
            if ($this->check_group($group)) {
                return true;
            }
        }

        return false;
    }

    public function getTokenAttribute()
    {
        $sign = hash_hmac('sha256', $this->username, config('app.key'));
        return base64_encode(implode('$', [$this->username, $sign]));
    }

    public function getTokenJsonAttribute()
    {
        return json_encode((object)[
            'token' => $this->token
        ]);
    }

    public static function is_logged_in()
    {
        return Services\Auth::isLoggedIn();
    }

    public static function IsLoggedIn()
    {
        return Services\Auth::isLoggedIn();
    }

    private static $_me = null;
    public static function me()
    {
        if (!isset(self::$_me)) {
            if (!\Session::has('username')) {
                if (!Services\Auth::isLoggedIn()) {
                    header('Location: http://' . $_SERVER['HTTP_HOST'] . '/login');
                    die();
                }
                $userInfo = Services\Auth::getUserInfo();
                \Session::set('username', $userInfo['https://codeday.xyz/username']);
                $me = self::fromS5Username(\Session::get('username'));
                if (!$me->logged_in_at) {
                    $me->wasFirstLogin = true;
                }
                $me->logged_in_at = Carbon::now();
                $me->save();
            } else {
                $me = self::fromS5Username(\Session::get('username'));
            }

            self::$_me = $me;
        }

        return self::$_me;
    }

    public static function fromToken($token)
    {
        list($username, $signature) = explode('$', base64_decode($token));
        if (hash_hmac('sha256', $username, config('app.key')) == $signature) {
            return self::where('username', '=', $username)->first();
        } else return null;
    }

    public static function fromOAuthToken($token) {
        $token = User\OAuthToken::where('access_token', '=', $token)->first();

        if(null == $token) {
            return null;
        } else {
            return $token->user;
        }
    }

    public static function fromS5Username($username)
    {
        $user = self::find($username);
        if (!$user) {
            $user = new self;
        }

        if (!$user->exists || $user->updated_at->diffInHours() > 1) {
            try {
                $s5_user = Services\Auth::getUserInfo($username);
                if (isset($s5_user)) {
                    if (isset($s5_user->phone_number)) {
                        $s5_user->phone = $s5_user->phone_number;
                    }
                    $s5_user->first_name = $s5_user->given_name;
                    $s5_user->last_name = $s5_user->family_name;
                    $s5_user->is_admin = count(array_filter($s5_user->permissions, function($e) { return $e === 'read:dashboard'; })) > 0;
                } else {
                    $s5_user = self::api()->User->get($username);
                }
            } catch (\Exception $ex) {
                return null;
            }

            $user->username = $s5_user->username;
            $user->first_name = $s5_user->first_name;
            $user->last_name = $s5_user->last_name;
            $user->email = $s5_user->email;
            if($s5_user->phone){ $user->phone = $s5_user->phone; }
            $user->is_admin = $s5_user->is_admin;
            $user->save();
        }

        return $user;
    }

    public function s5Update()
    {
        try {
            $s5_user = Services\Auth::getUserInfo($this->username);
            if (!isset($s5_user)) {
                $s5_user = self::api()->User->get($this->username);
            }
        } catch (\Exception $ex) {
            return null;
        }

        $this->first_name = $s5_user->first_name;
        $this->last_name = $s5_user->last_name;
        $this->email = $s5_user->email;
        $this->phone = $s5_user->phone;
        $this->is_admin = $s5_user->is_admin;
        $this->save();
    }

    public function getInternalEmailAttribute()
    {
        return $this->username.'@srnd.org';
    }

    public function managedEvents()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Batch\Event', 'manager_username', 'username');
    }

    public function applications()
    {
        return $this->hasMany('\CodeDay\Clear\Models\Application', 'admin_username', 'username');
    }

    public function getCurrentManagedEventsAttribute()
    {
        return $this->getManagedEvents(Batch::Managed());
    }

    public function getLoadedManagedEventsAttribute()
    {
        return $this->getManagedEvents(Batch::Loaded());
    }

    public function getManagedEvents(Batch $batch)
    {
        return Batch\Event::select('batches_events.*')
                        ->where('batch_id', '=', $batch->id)
                        ->join('users_grants', 'users_grants.batches_event_id', '=', 'batches_events.id', 'left')
                        ->where(function($query) {
                            $query->where('users_grants.username', '=', $this->username)
                                ->orWhere('batches_events.manager_username', '=', $this->username)
                                ->orWhere('batches_events.evangelist_username', '=', $this->username)
                                ->orWhere('batches_events.coach_username', '=', $this->username);
                        })
                        ->groupBy('batches_events.id')
                        ->get();
    }

    public function getManagedBatchesAttribute()
    {
        return Batch::select('batches.*')
            ->join('batches_events', 'batches_events.batch_id', '=', 'batches.id', 'left')
            ->join('users_grants', 'users_grants.batches_event_id', '=', 'batches_events.id', 'left')
            ->where(function($query) {
                $query->where('users_grants.username', '=', $this->username)
                    ->orWhere('batches_events.manager_username', '=', $this->username)
                    ->orWhere('batches_events.evangelist_username', '=', $this->username);
            })
            ->groupBy('batches.id')
            ->get();
    }

    private static $_s5 = null;
    private static function api()
    {
        if (!isset(self::$_s5)) {
            self::$_s5 = new \s5\API(\Config::get('s5.token'), \Config::get('s5.secret'));
        }

        return self::$_s5;
    }
}

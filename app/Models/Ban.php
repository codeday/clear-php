<?php
namespace CodeDay\Clear\Models;

use \Carbon\Carbon;

class Ban extends \Eloquent {
    protected $table = 'bans';

    public function getDates()
    {
        return ['created_at', 'updated_at', 'expires_at'];
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function getReasonNameAttribute()
    {
        switch ($this->reason)
        {
            case "drugs":
                return "Drugs and Alcohol";
            case "codeofconduct":
                return "Other Code of Conduct";
            case "noshow":
                return "Repeat Noshow";
            case "young":
                return "Too Young";
            case "old":
                return "Too Old";
            default:
                return ucfirst($this->reason);
        }
    }

    public function getReasonTextAttribute()
    {
        switch ($this->reason)
        {
            case "codeofconduct":
                return "You cannot register because of a past Code of Conduct violation.";
            case "chargeback":
                return "You cannot register because, in the past, you disputed a CodeDay charge with your bank"
                        . " which resulted in a chargeback.";
            case "noshow":
                return "You were blocked from registering because you have a history of registering and not attending"
                        . " CodeDay. Because of limited space, we want to ensure registrants attend.";
            case "young":
                return "CodeDay is an event for mature students, largely in high school and college. You will need to"
                        . " wait until you are older to register.";
            case "old":
                return "CodeDay is an event for students between 16-22; unfortunately we are unable to admit adults.";
            case "recruiter":
                return "CodeDay is an event for students, not recruiters. If you'd like to recruit at CodeDay, please"
                        . " contact sponsorships@codeday.org.";
            case "other":
                return "You cannot register because of a past violation of our rules.";
            default:
                return 'You cannot register becase of a past '.$this->reason_name.' violation';
        }
    }

    public function getIsEnforcedAttribute()
    {
        return $this->expires_at == null || $this->expires_at->isFuture();
    }

    public static function GetBannedReasonOrNull($email)
    {
        return self
            ::where('email', '=', trim($email))
            ->where(function ($query) {
                $query
                    ->where('expires_at', '>', Carbon::now()->format('Y-m-d H:i:s'))
                    ->orWhereNull('expires_at');
            })
            ->first();
    }

    public function creator()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'created_by_username', 'username');
    }
}

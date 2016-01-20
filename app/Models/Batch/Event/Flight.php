<?php
namespace CodeDay\Clear\Models\Batch\Event;

class Flight extends \Eloquent {
    protected $table = 'batches_events_flights';

    public function event()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\Batch\Event', 'batches_event_id', 'id');
    }

    public function traveler()
    {
        return $this->belongsTo('\CodeDay\Clear\Models\User', 'traveler_username', 'username');
    }

    public function getDates()
    {
        return ['created_at', 'updated_at', 'arrives_at', 'departs_at', 'checkin_reminder_sent_at'];
    }

    public function getCheckinAllowedAtAttribute()
    {
        return $this->departs_at->addDays(-1);
    }

    public function getRelatedFlightsAttribute()
    {
        return self
            ::where('confirmation_code', '=', $this->confirmation_code)
            ->where('direction', '=', $this->direction)
            ->where('traveler_username', '=', $this->traveler_username)
            ->where('batches_event_id', '=', $this->batches_event_id)
            ->get();
    }

    public function getCheckinLinkAttribute()
    {
        switch (strtoupper($this->airline)) {
            case "AA":
            case "AAL":
                return "https://www.aa.com/reservation/flightCheckInViewReservationsAccess.do";
            case "US":
            case "USA":
                return "http://checkin.usairways.com/";
            case "UA":
                return "http://www.united.com/travel/checkin/start.aspx";
            case "AS":
            case "ASA":
                return "https://webselfservice.alaskaair.com/checkinweb/";
            case "WN":
            case "SWA":
                return "https://www.southwest.com/flight/retrieveCheckinDoc.html";
            case "F9":
            case "FFT":
                return "https://content.flyfrontier.com/manage-travel/online-check-in";
            case "VX":
            case "VRD":
                return "https://www.virginamerica.com/flight-check-in";
            case "DL":
            case "DAL":
                return "http://www.delta.com/content/www/en_US/traveling-with-us/check-in/options.html";
            case "B6":
            case "JBU":
                return "https://book.jetblue.com/B6.myb/checkIn.html";
            case "NK":
            case "NKS":
                return "https://www.spirit.com/onlinecheckin.aspx";
            default:
                break;

        }
    }
}
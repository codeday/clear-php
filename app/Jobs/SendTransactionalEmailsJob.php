<?php

namespace CodeDay\Clear\Jobs;

use CodeDay\Clear\Models;
use CodeDay\Clear\Services;
use Carbon\Carbon;

class SendTransactionalEmailsJob extends Job
{
    public function handle()
    {
        foreach (Models\Batch::Loaded()->registrations as $registration) {
            try{
            	$this->sendEmailsForRegistration($registration);
            }catch(\Exception $ex){
            	//$raygun = new \Raygun4php\RaygunClient(\Config::get("raygun.api_key"));
            	//$raygun->SendException($ex, ["SendTransactionalEmailsJob"]);
            }
        }
    }

    private function sendEmailsForRegistration(Models\Batch\Event\Registration $registration)
    {
        $allEmails = $this->getEmailsForRegistration($registration);
        $sentEmails = Models\TransactionalEmail::where('batches_events_registration_id', '=', $registration->id)->get();
        $sentEmailIds = array_map(function($a){ return $a->email_id; }, iterator_to_array($sentEmails));

        foreach ($allEmails as $email) {
            try {
                // Has the email been sent?
                if (in_array($email->id, $sentEmailIds)) continue;

                // Should the email be sent now? (Is the time in the past)?
                if ($email->when->isFuture()) continue;

                // Mark the email as sent.
                $record = new Models\TransactionalEmail;
                $record->batches_events_registration_id = $registration->id;
                $record->email_id = $email->id;
                $record->save();

                // Send the email
                $contentText = null;
                $contentHtml = null;
                $tplBindings = ['registration' => $registration];
                if (isset($email->text)) $contentText = \View::make($email->text, $tplBindings);
                if (isset($email->html)) $contentHtml = \View::make($email->html, $tplBindings);

                Services\Email::SendOnQueue(
                    $email->from, $email->from_e,
                    $email->to, $email->to_e,
                    $email->subject,
                    $contentText, $contentHtml, $email->marketing
                );
            } catch (\Exception $ex) {}
        }
    }

    /**
     * Gets a list of all emails which should be sent for a specified registrant. Does not check if they're sent.
     *
     * @param   Models\Batch\Event\Registration $registration   The registration to get the emails for.
     * @return  object[]                                        A list of all emails to send for the user.
     */
    private function getEmailsForRegistration(Models\Batch\Event\Registration $registration)
    {
        $config = $this->getEmailsJson()->registration;
        $type = $registration->type;
        if (!$type) throw new \Exception("Ticket type is not set for ".$registration->email);
        
        $relevantConfig = null;
        if (isset($config->$type)) {
        	$relevantConfig = array_merge($config->all, $config->$type);
        } else {
        	$relevantConfig = $config->all;
        }
        
        $allEmails = [];

        foreach ($relevantConfig as $emailConfig) {
            // Calculate the time.
            $when = $this->getSendTimeForRegistration($emailConfig->when, $registration);
            if (isset($emailConfig->work) && $emailConfig->work) {
                $when = $this->getWorkHourSendTime($when);
            }

            // Check if the time is before the user registered: if it is, make sure the email should still be sent.
            if ($when->lte($registration->created_at) && isset($emailConfig->late) && !$emailConfig->late) continue;

            // Check if the email is going to the user's parents.
            $to   = $registration->name;
            $to_e = $registration->email;
            if (isset($emailConfig->target) && $emailConfig->target == 'parent') {
                if ($registration->parent_email !== null) {
                    $to = $registration->parent_name;
                    $to_e = $registration->parent_email;
                } else continue;
            }

            // Add the calculated email config.
            $email = [
                'id'            => $emailConfig->id,
                'when'          => $when,
                'subject'       => $emailConfig->subject,
                'to'            => $to,
                'to_e'          => $to_e,
                'marketing'     => isset($emailConfig->marketing) && $emailConfig->marketing
            ];
            if (isset($emailConfig->text)) $email['text'] = $emailConfig->text;
            if (isset($emailConfig->html)) $email['html'] = $emailConfig->html;
            if (isset($emailConfig->from_n)) {
                $email['from'] = $emailConfig->from_n;
            } else {
                $email['from'] = 'CodeDay '.$registration->event->name;
            }
            if (isset($emailConfig->from_e)) {
                $email['from_e'] = $emailConfig->from_e;
            } else {
                $email['from_e'] = $registration->event->webname.'@codeday.org';
            }

            $allEmails[] = (object)$email;
        }

        return $allEmails;
    }

    /**
     * Decodes the email configuration JSON and returns the result.
     *
     * @return object   The email configuration.
     */
    private function getEmailsJson()
    {
        $json = file_get_contents(config_path().DIRECTORY_SEPARATOR.'emails.json');
        return json_decode($json);
    }

    /**
     * Makes sure the send time is during the reasonable working day to appear as if sent by a person.
     *
     * @param   Carbon  $time   The original send time.
     * @return  Carbon          Adjusted send time.
     */
    private function getWorkHourSendTime(Carbon $time)
    {
        $officeHoursStart = 8;
        $officeHoursEnd = 19;

        // Add a random offset to make it seem less automated
        $time = $time->addHours(rand(0,2))->addMinutes(rand(0,60));

        if ($time->hour < $officeHoursStart) {
            $time->hour = $officeHoursStart;
            $time->addHours(rand(0,2));
        } else if ($time->hour > $officeHoursEnd) {
            $time = $time->addDay();
            $time->hour = $officeHoursStart;
            $time->addHours(rand(0,2));
        }

        return $time;
    }

    /**
     * Gets the send time for a specific registration, given a time interval with a specified offset reference.
     *
     * Gets the send time for a specific registration. String format is "(+/-)1(s/h/m/d/w/m)", where "+" means "after
     * the attendee registered", and "-" means "before the event starts" (default, if unspecified, is "+"). "(s/h/...)"
     * specifies the interval, e.g. "1m" is the same as "60s" or just "60".
     *
     * @param   string                          $timeString     The time string.
     * @param   Models\Batch\Event\Registration $registration   The registration to calculate the time for.
     * @return  Carbon                                          The send time.
     */
    private function getSendTimeForRegistration($timeString, Models\Batch\Event\Registration $registration)
    {
        $direction = '+';
        if (!is_numeric(substr($timeString, 0, 1))) {
            $direction = substr($timeString, 0, 1);
            $timeString = substr($timeString, 1);
        }
        
        $offset = $this->getSecondsForTimeString($timeString);

        switch ($direction) {
            case '+':
                return $registration->created_at->addSeconds($offset);
            case '-':
                return $registration->event->batch->starts_at->addSeconds(-1*$offset);
            default:
                throw new \Exception("$direction is not a valid time direction.");
        }
    }

    /**
     * Turns a time in interval format (e.g. 2m) into seconds (e.g. 120).
     *
     * @param   string  $timeString The time in time interval format.
     * @return  int                 The time in seconds.
     *
     */
    private function getSecondsForTimeString($timeString)
    {
        $unit = 's';
        if (!is_numeric(substr($timeString, -1))) {
            $unit = substr($timeString, -1);
            $timeString = substr($timeString, 0, count($timeString));
        }


        switch ($unit) {
            case "s":
                return $timeString;
            case "m":
                return $timeString*60;
            case "h":
                return $timeString*60*60;
            case "d":
                return $timeString*60*60*24;
            case "w":
                return $timeString*60*60*24*7;
            case "m":
                return $timeString*60*60*24*31;
            default:
                throw new \Exception("Time unit $unit is not recognized.");
        }
    }
}

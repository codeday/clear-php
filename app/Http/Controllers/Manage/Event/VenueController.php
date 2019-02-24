<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class VenueController extends \CodeDay\Clear\Http\Controller {
    public function getIndex()
    {
        return \View::make('event/venue');
    }

    public function postIndex()
    {
        $event = \Route::input('event');

        $event->venue_name = \Input::get('venue_name') ? \Input::get('venue_name') : null;
        $event->venue_address_1 = \Input::get('venue_address_1') ? \Input::get('venue_address_1') : null;
        $event->venue_address_2 = \Input::get('venue_address_2') ? \Input::get('venue_address_2') : null;
        $event->venue_city = \Input::get('venue_city') ? \Input::get('venue_city') : null;
        $event->venue_state = \Input::get('venue_state') ? \Input::get('venue_state') : null;
        $event->venue_postal = \Input::get('venue_postal') ? \Input::get('venue_postal') : null;
        $event->venue_country= \Input::get('venue_country') ? strtoupper(\Input::get('venue_country')) : null;
        $event->max_registrations = \Input::get('max_registrations') ? \Input::get('max_registrations') : null;
        $event->loaners_available = \Input::get('loaners_available') ? \Input::get('loaners_available') : 0;

        $event->venue_contact_first_name = \Input::get('venue_contact_first_name') ? \Input::get('venue_contact_first_name') : null;
        $event->venue_contact_last_name = \Input::get('venue_contact_last_name') ? \Input::get('venue_contact_last_name') : null;
        $event->venue_contact_email = \Input::get('venue_contact_email') ? \Input::get('venue_contact_email') : null;
        $event->venue_contact_phone = \Input::get('venue_contact_phone') ? \Input::get('venue_contact_phone') : null;

        if ($event->venue_country == 'UN') {
            $event->venue_country = 'US';
        }


        if (\Request::hasFile('venue_agreement')) {
            $s3 = \Aws\S3\S3Client::factory([
                'credentials' => [
                    'key' => \Config::get('aws.key'),
                    'secret' => \Config::get('aws.secret')
                ],
                'version' => '2006-03-01',
                'region' => 'us-west-1'
            ]);
            $uploadPath = 'codeday/clear/venue-agreement-'.$event->id.'-'.time().'.pdf';
            $result = $s3->putObject(array(
                'Bucket'       => \Config::get('aws.s3.assetsBucket'),
                'Key'          => $uploadPath,
                'Body'         => file_get_contents(\Request::file('venue_agreement')->getRealPath()),
                'ContentType'  => 'application/pdf',
                'ACL'          => 'public-read',
                'Metadata'     => [
                    'Content-Type' => 'application/pdf'
                ]
            ));

            $event->venue_agreement = \Config::get('aws.s3.assetsUrl').$uploadPath;
        }

        $event->save();
        \Session::flash('status_message', 'Venue updated');
        return \Redirect::to('/event/'.$event->id.'/venue');
    }
} 

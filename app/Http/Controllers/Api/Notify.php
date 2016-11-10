<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;

class Notify extends \CodeDay\Clear\Http\Controller {
    protected $requiresApplication = false;

    public function optionsSubscribe()
    {
        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');
        return $response;
    }

    public function postSubscribe()
    {
        $batch = Models\Batch::find(\Input::get('batch'));
        $region = Models\Region::find(\Input::get('region'));
        $event = Models\Batch\Event::find(\Input::get('event'));
        $email = \Input::get('email');

        $response = \Response::make();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', '*');
        $response->headers->set('Content-type', 'text/javascript');


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->setStatusCode(400);
            $response->setContent(json_encode([
                'status' => 400,
                'message' => 'Invalid email.'
            ]));
            return $response;
        }

        if ($event) {
            $region = null;
            $batch = null;
        } elseif ($region) {
            $batch = null;
        } elseif (!$batch) {
            $batch = Models\Batch::Loaded();
        }

        $exists_query = Models\Notify::where('email', '=', $email);
        if ($event) {
            $exists_query->where('batches_event_id', '=', $event->id);
        } elseif ($region) {
            $exists_query->where('region_id', '=', $region->id);
        } elseif ($batch) {
            $exists_query->where('batch_id', '=', $batch->id);
        }

        if (!$exists_query->exists()) {
            $notify_model = new Models\Notify;
            $notify_model->email = $email;

            if ($event) {
                $notify_model->batches_event_id = $event->id;
            } elseif ($region) {
                $notify_model->region_id = $region->id;
            } elseif ($batch) {
                $notify_model->batch_id = $batch->id;
            }
            $notify_model->save();
        }

        $response->setContent(json_encode([
            'status' => 200,
            'message' => 'Subscribed'
        ]));
        return $response;
    }
} 
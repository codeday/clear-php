<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Tests;

class Notify extends Tests\ApiTestCase {
    public function testSubscribe()
    {
        $event = Models\Batch\Event::first();
        $email = str_random(12).'@example.org';

        $response = $this->call('POST', '/api/notify/subscribe', [
            'event' => $event->id,
            'email' => $email
        ]);
        $this->assertValidOkApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_object($data),
            'Notify response was not an object.');
        $this->assertEquals(200, $data->status,
            'Notify endpoint returned non-200 response in JSON.');
        $this->assertNotNull($data->message,
            'Notify endpoint did not send message.');

        $createdModel = Models\Notify::where('email', '=', $email)->first();
        $this->assertNotNull($createdModel,
            'Notify subscriber was not added');
        $this->assertEquals($event->id, $createdModel->batches_event_id,
            'Notify subscriber was not subscribed to the correct event');
        $createdModel->delete();
    }

    public function testInvalidSubscribe()
    {
        $event = Models\Batch\Event::first();
        $email = str_random(12);

        $response = $this->call('POST', '/api/notify/subscribe', [
            'event' => $event->id,
            'email' => $email
        ]);
        $this->assertValidApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_object($data),
            'Notify response was not an object.');
        $this->assertEquals(400, $data->status,
            'Notify endpoint returned non-400 response in JSON for invalid email.');
        $this->assertNotNull($data->message,
            'Notify endpoint did not send message.');

        $createdModel = Models\Notify::where('email', '=', $email)->first();
        $this->assertNull($createdModel,
            'Notify subscriber was added');
    }
}
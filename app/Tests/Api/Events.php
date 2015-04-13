<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Tests;

class Events extends Tests\ApiTestCase {
    public function testIndex()
    {
        $app = new Models\Application;
        $app->name = str_random(12);
        $app->description = str_random(12);
        $app->public = str_random(12);
        $app->private = str_random(12);
        $app->permission_admin = false;
        $app->permission_internal = false;
        $app->save();

        $response = $this->call('GET', '/api/events', ['public' => $app->public, 'private' => $app->private]);
        $this->assertValidOkApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_array($data),
            'Event list was not an array.');
        $this->assertGreaterThan(0, count($data),
            'Event list did not contain any events.');
        $this->assertEventValid($data[0]);

        $app->delete();
    }

    public function testCurrent()
    {
        $app = new Models\Application;
        $app->name = str_random(12);
        $app->description = str_random(12);
        $app->public = str_random(12);
        $app->private = str_random(12);
        $app->permission_admin = false;
        $app->permission_internal = false;
        $app->save();

        $event = Models\Batch\Event::first();

        $response = $this->call('GET', '/api/event/'.$event->id, ['public' => $app->public, 'private' => $app->private]);
        $this->assertValidOkApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_object($data),
            'Event was not an object.');
        $this->assertEventValid($data, true);

        $app->delete();
    }

    private function assertEventValid($event, $sparse = true)
    {
        $this->assertNotNull($event->id,
            'Event was missing required attribute (id)');
        $this->assertNotNull($event->name,
            'Event was missing required attribute (name)');
        $this->assertNotNull($event->starts_at,
            'Event was missing required attribute (starts_at)');
        $this->assertNotNull($event->ends_at,
            'Event was missing required attribute (ends_at)');
        $this->assertNotNull($event->region_name,
            'Event was missing required attribute (region_name)');
        $this->assertNotNull($event->webname,
            'Event was missing required attribute (webname)');
        $this->assertNotNull($event->stripe_public_key,
            'Event was missing required attribute (stripe_public_key)');
    }
}
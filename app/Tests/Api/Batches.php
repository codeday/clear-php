<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Tests;

class Batches extends Tests\ApiTestCase {
    public function testIndex()
    {
        $response = $this->call('GET', '/api/batches');
        $this->assertValidOkApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_array($data),
            'Batches list was not an array.');
        $this->assertGreaterThan(0, count($data),
            'Batches list did not contain any batches.');
        $this->assertBatchValid($data[0]);
    }

    public function testCurrent()
    {
        $response = $this->call('GET', '/api/batches/current');
        $this->assertValidOkApiResponse($response);

        $data = json_decode($response->getContent());

        $this->assertTrue(is_object($data),
            'Current batch was not an object.');
        $this->assertBatchValid($data, true);
    }

    private function assertBatchValid($batch,  $sparse = true)
    {
        $this->assertNotNull($batch->id,
            'Batch was missing required attribute (id)');
        $this->assertNotNull($batch->name,
            'Batch was missing required attribute (name)');
        $this->assertNotNull($batch->starts_at,
            'Batch was missing required attribute (starts_at)');
        $this->assertNotNull($batch->ends_at,
            'Batch was missing required attribute (ends_at)');
        $this->assertNotNull($batch->is_loaded,
            'Batch was missing required attribute (is_loaded)');

        $this->assertEquals('boolean', gettype($batch->is_loaded),
            'Batch property "is_loaded" was of incorrect type.');

        if (!$sparse) {
            $this->assertNotNull($batch->events,
                'Batch did not contain events');
            $this->assertTrue(is_array($batch->events),
                'Batch property "events" was of incorrect type.');
            $this->assertGreaterThan(0, $batch->events,
                'Batch did not contain events');
            $this->assertNotNull($batch->events[0]->id,
                'Batch event did not contain id');
        }
    }
}
<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Tests;

class Register extends Tests\ApiTestCase {
    public function testPromotion()
    {
        $event = Models\Batch\Event::first();
        $code = str_random(12);
        $allowed_uses = 10;

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper($code);
        $promotion->notes = '';
        $promotion->percent_discount = 20;
        $promotion->expires_at = time() + 86400;
        $promotion->allowed_uses = $allowed_uses;
        $promotion->save();

        $response = $this->call('GET', '/api/register/'.$event->id.'/promotion', [
            'code' => $code
        ]);

        $this->assertValidOkApiResponse($response);
        $data = json_decode($response->getContent());

        $this->assertEquals($allowed_uses, $data->remaining_uses,
            'Promotion remaining uses does not match with set uses.');
        $this->assertNotNull($data->cost,
            'Promotion cost should not be null.');
        $this->assertFalse($data->expired,
            'Promotion incorrectly expired');
        $this->assertEquals(20, $data->discount,
            'Discount percent is incorrect');

        $promotion->delete();
    }

    public function testExpiredPromotion()
    {
        $event = Models\Batch\Event::first();
        $code = str_random(12);

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper($code);
        $promotion->notes = '';
        $promotion->percent_discount = 20;
        $promotion->expires_at = time();
        $promotion->allowed_uses = null;
        $promotion->save();

        $response = $this->call('GET', '/api/register/'.$event->id.'/promotion', [
            'code' => $code
        ]);

        $this->assertValidOkApiResponse($response);
        $data = json_decode($response->getContent());

        $this->assertTrue($data->expired,
            'Expired promotion does not appear expired');

        $promotion->delete();
    }
}
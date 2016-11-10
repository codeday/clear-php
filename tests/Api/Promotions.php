<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Tests;

class Promotions extends Tests\ApiTestCase {
    public function testGetPromotion()
    {
      $event = Models\Batch\Event::first();

      $promotion = new Models\Batch\Event\Promotion;
      $promotion->batches_event_id = $event->id;
      $promotion->code = "CODEDAY";
      $promotion->notes = "some notes for this test code";
      $promotion->percent_discount = "20";
      $promotion->expires_at = null;
      $promotion->allowed_uses = null;
      $promotion->created_by_user = "afakeuser";
      $promotion->save();

      $app = new Models\Application;
      $app->name = str_random(12);
      $app->description = str_random(12);
      $app->public = str_random(12);
      $app->private = str_random(12);
      $app->permission_admin = true; // promotions require admin permission
      $app->permission_internal = false;
      $app->save();

      $response = $this->call('GET', '/api/promotion/'.$promotion->id, ['public' => $app->public, 'private' => $app->private]);
      $this->assertValidOkApiResponse($response);

      $data = json_decode($response->getContent());

      $this->assertTrue(is_object($data),
          'Notify response was not an object.');
      $this->assertEquals(0, $data->uses,
          'Uses was not 0.');
      $this->assertEquals("CODEDAY", $data->code,
          'Promotion code was not CODEDAY.');
      $this->assertEquals("afakeuser", $data->created_by_user,
          'Promotion was not created by afakeuser.');

      $app->delete();
      $promotion->delete();
    }

    public function testNewPromotion(){
      $event = Models\Batch\Event::first();

      $app = new Models\Application;
      $app->name = str_random(12);
      $app->description = str_random(12);
      $app->public = str_random(12);
      $app->private = str_random(12);
      $app->permission_admin = true; // promotions require admin permission
      $app->permission_internal = false;
      $app->save();

      $response = $this->call('POST', '/api/promotions/new', [
        'public' => $app->public,
        'private' => $app->private,
        'event' => $event->id,
        'code' => 'cOdeDAy', // this should be normalized to "CODEDAY" when created
        'notes' => 'test test',
        'username' => 'afakeuser'
      ]);
      $this->assertValidOkApiResponse($response);

      $promotion = Models\Batch\Event\Promotion::first();

      $this->assertEquals("CODEDAY", $promotion->code,
          'Promotion code was not CODEDAY.');
      $this->assertEquals("afakeuser", $promotion->created_by_user,
          'Promotion was not created by afakeuser.');
      $this->assertEquals("20", $promotion->percent_discount,
          'Promotion discount was not 20%.');

      $promotion->delete();
      $app->delete();
    }
}

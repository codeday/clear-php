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

    public function testForcePricePromotion()
    {
        $event = Models\Batch\Event::first();
        $code = str_random(12);

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper($code);
        $promotion->notes = '';
        $promotion->force_price = 1.0;
        $promotion->save();

        $response = $this->call('GET', '/api/register/'.$event->id.'/promotion', [
            'code' => $code
        ]);

        $this->assertValidOkApiResponse($response);
        $data = json_decode($response->getContent());

        $this->assertNotNull($data->cost,
            'Promotion cost should be $1.');
        $this->assertEquals(90, $data->discount,
            'Discount percent should be 90%');

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

    public function testRegisterOk()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $token = \Stripe\Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => "01",
                "exp_year" => date('y') + 2
            ]
        ])['id'];
        $data = $this->register($event, ['test'], ['test'], [$r], 10, '', $token);
        $this->assertEquals(200, $data->status, $data->message ?? null);
        $this->assertNotNull($data->ids);

        $model = Models\Batch\Event\Registration::where('email', '=', $r)->firstOrFail();
        $this->assertEquals($model->id, $data->ids[0], 'API returned incorrect ID');
        $this->assertEquals($model->type, 'student', 'Type not set to student');
        $this->assertNotNull($model->stripe_id, 'Funds not charged');
        $this->assertEquals(1000, \Stripe\Charge::retrieve($model->stripe_id)->amount, 'Charge amount was not $10');
        
        $model->forcedelete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testCad()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->venue_country = 'CA';
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        \Stripe\Stripe::setApiKey(\Config::get('stripe.secret'));
        $token = \Stripe\Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => "01",
                "exp_year" => date('y') + 2
            ]
        ])['id'];
        $data = $this->register($event, ['test'], ['test'], [$r], 10, '', $token);
        $this->assertEquals(200, $data->status, $data->message ?? null);
        $this->assertNotNull($data->ids);

        $model = Models\Batch\Event\Registration::where('email', '=', $r)->firstOrFail();
        $this->assertEquals('cad', \Stripe\Charge::retrieve($model->stripe_id)->currency, 'Currency was not canadian');
        $model->forcedelete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testInvalidEmail()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();


        $r = strtolower(str_random(10)).'@srnd.teej';
        $r2 = strtolower(str_random(10));

        $data = $this->register($event, ['test', 'test'], ['test', 'test'], [$r, $r2], 20);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('missing_info', $data->error);

        $model = Models\Batch\Event\Registration::where('email', '=', $r2)->first();
        $this->assertNull($model, 'Registration created for failed validity check');
        if ($model) $model->forcedelete();

        $model = Models\Batch\Event\Registration::where('email', '=', $r)->first();
        $this->assertNull($model, 'Registration created when later record failed validity check.');
        if ($model) $model->forcedelete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testClosedEvent()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = false;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $data = $this->register($event, ['test'], ['test'], [$r]);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('capacity', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testCreationOnFail()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = false;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $data = $this->register($event, ['test'], ['test'], [$r]);

        $model = Models\Batch\Event\Registration::where('email', '=', $r)->first();
        $this->assertNull($model, 'Registration created for closed event');
        if ($model) $model->forcedelete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testSanitization()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $data = $this->register($event, [" lower UPPER\t"], [" mcdaniel von smith\t"], [' '.strtoupper($r)."\t"]);
        $this->assertEquals(200, $data->status, $data->message ?? null);
        $this->assertNotNull($data->ids);

        $model = Models\Batch\Event\Registration::where('email', '=', $r)->firstOrFail();
        $this->assertEquals('Lower Upper', $model->first_name, 'First name not sanitized properly');
        $this->assertEquals('McDaniel von Smith', $model->last_name, 'Last name not sanitized properly');
        $this->assertEquals($r, $model->email, 'Email not sanitized properly');
        $model->forcedelete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testMissingName()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $data = $this->register($event, [''], ['test'], [$r]);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('missing_info', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testMissingEmail()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $data = $this->register($event, ['test'], ['test'], ['']);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('missing_info', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testBadEmail()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $data = $this->register($event, ['test'], ['test'], ['foo']);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('missing_info', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testBadName()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $data = $this->register($event, ["\t "], ['test'], ['foo']);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('missing_info', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testBadQuote()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 0);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('quote_mismatch', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testGiftcard()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $gc = new Models\GiftCard;
        $gc->code = strtoupper(str_random(5));
        $gc->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 0, strtolower($gc->code));
        $this->assertEquals(200, $data->status, $data->message ?? '');

        $gc = Models\GiftCard::where('code', '=', $gc->code)->firstOrFail();
        $this->assertEquals($data->ids[0], $gc->batches_events_registration_id, 'Giftcard not marked used');

        $gc->delete();

        Models\Batch\Event\Registration::where('id', '=', $data->ids[0])->forcedelete();
        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testPromoValid()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper(str_random(5));
        $promotion->notes = '';
        $promotion->percent_discount = 50;
        $promotion->expires_at = time() + 86400;
        $promotion->allowed_uses = 10;
        $promotion->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 5, strtolower($promotion->code));
        $this->assertEquals(200, $data->status, $data->message ?? '');

        $reg = Models\Batch\Event\Registration::where('id', '=', $data->ids[0])->firstOrFail();
        $this->assertEquals($promotion->id, $reg->batches_events_promotion_id, "Promo not marked used");
        $this->assertEquals("student", $reg->type, "Registration did not take default type");

        $reg->forcedelete();
        $promotion->delete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testPromoForcedPrice()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper(str_random(5));
        $promotion->force_price = 1.0;
        $promotion->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 1, strtolower($promotion->code));
        $this->assertEquals(200, $data->status, $data->message ?? '');

        $reg = Models\Batch\Event\Registration::where('id', '=', $data->ids[0])->firstOrFail();

        $reg->forcedelete();
        $promotion->delete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testPromoType()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper(str_random(5));
        $promotion->notes = '';
        $promotion->percent_discount = 50;
        $promotion->expires_at = time() + 86400;
        $promotion->allowed_uses = 10;
        $promotion->type = "vip";
        $promotion->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 5, strtolower($promotion->code));
        $this->assertEquals(200, $data->status, $data->message ?? '');

        $reg = Models\Batch\Event\Registration::where('id', '=', $data->ids[0])->firstOrFail();
        $this->assertEquals($promotion->id, $reg->batches_events_promotion_id, "Promo not marked used");
        $this->assertEquals($promotion->type, $reg->type, "Registration did not take promo type");

        $reg->forcedelete();
        $promotion->delete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testPromoInvalid()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $promotion = new Models\Batch\Event\Promotion;
        $promotion->batches_event_id = $event->id;
        $promotion->code = strtoupper(str_random(5));
        $promotion->notes = '';
        $promotion->percent_discount = 50;
        $promotion->expires_at = time() - 86400;
        $promotion->allowed_uses = 1;
        $promotion->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 5, strtolower($promotion->code));
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('promo', $data->error);

        $promotion->delete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testPromoGibberish()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 20;
        $event->allow_registrations = true;
        $event->save();

        $data = $this->register($event, ["test"], ['test'], ['foo@foo.com'], 5, "FAIL");
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('promo', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testCappedEvent()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 0;
        $event->allow_registrations = true;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $data = $this->register($event, ['test'], ['test'], [$r]);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('capacity', $data->error);

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }

    public function testBan()
    {
        $event = $this->getFutureEvent();
        $event->max_registrations = 10;
        $event->allow_registrations = true;
        $event->save();

        $r = strtolower(str_random(10)).'@srnd.org';

        $ban = new Models\Ban;
        $ban->first_name = "test";
        $ban->last_name = "test";
        $ban->email = $r;
        $ban->reason = 'other';
        $ban->details = '';
        $ban->created_by_username = 'tylermenezes';
        $ban->save();

        $data = $this->register($event, ['test'], ['test'], [$r]);
        $this->assertEquals(500, $data->status, 'API request succeeded where should have failed');
        $this->assertEquals('banned', $data->error);

        $ban->delete();

        $batch = $event->batch;
        $event->forcedelete();
        $batch->delete();
    }




    private function register($event, $first_names, $last_names, $emails, $quote = 10, $code = '', $token = 'tok_hhh')
    {
        $response = $this->call('POST', '/api/register/'.$event->id.'/register', [
            'access_token' => 'testtesttesttesttesttest',
            'card_token' => $token,
            'emails' => $emails,
            'first_names' => $first_names,
            'last_names' => $last_names,
            'quoted_price' => $quote,
            'code' => $code
        ]);
        $this->assertValidOkApiResponse($response);
        return json_decode($response->getContent());
    }

    private function getFutureEvent()
    {
        foreach (Models\Batch::all() as $batch) { $batch->is_loaded = false; $batch->save(); }

        $batch = new Models\Batch;
        $batch->starts_at = time() + (60*60*24*12);
        $batch->allow_registrations = true;
        $batch->name = "Test";
        $batch->is_loaded = true;
        $batch->save();

        $event = new Models\Batch\Event;
        $event->region_id = Models\Region::first()->id;
        $event->batch_id = $batch->id;
        $event->save();

        return $event;
    }
}

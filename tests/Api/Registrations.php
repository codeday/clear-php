<?php
namespace CodeDay\Clear\Tests\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Tests;

class Registrations extends Tests\ApiTestCase {
    public function testGetRegistration()
    {
      $event = Models\Batch\Event::first();
      $email = str_random(12).'@example.org';

      $reg = new Models\Batch\Event\Registration;
      $reg->id = str_random(12); // why
      $reg->batches_event_id = $event->id;
      $reg->first_name = "Cool";
      $reg->last_name = "Guy";
      $reg->email = $email;
      $reg->type = "student";
      $reg->save();

      $app = new Models\Application;
      $app->name = str_random(12);
      $app->description = str_random(12);
      $app->public = str_random(12);
      $app->private = str_random(12);
      $app->permission_admin = true; // registrations require admin permission
      $app->permission_internal = false;
      $app->save();

      $response = $this->call('GET', '/api/registration/'.$reg->id, ['public' => $app->public, 'private' => $app->private]);
      $this->assertValidOkApiResponse($response);

      $data = json_decode($response->getContent());

      $this->assertTrue(is_object($data),
          'Notify response was not an object.');
      $this->assertEquals("Cool", $data->first_name,
          'First name was not "Cool".');
      $this->assertEquals("Guy", $data->last_name,
          'Last name was not "Guy".');
      $this->assertEquals("student", $data->type,
          'Ticket type was not student.');
      $this->assertEquals($email, $data->email,
          'Email was not equal to '.$email.'.');

      $app->delete();
      $reg->delete();
    }

    public function testGetRegistrationByEmail()
    {
      $event = Models\Batch\Event::first();
      $email = str_random(12).'@example.org';

      $reg = new Models\Batch\Event\Registration;
      $reg->batches_event_id = $event->id;
      $reg->first_name = "Cool";
      $reg->last_name = "Guy";
      $reg->email = $email;
      $reg->type = "student";
      $reg->save();

      $app = new Models\Application;
      $app->name = str_random(12);
      $app->description = str_random(12);
      $app->public = str_random(12);
      $app->private = str_random(12);
      $app->permission_admin = true; // promotions require admin permission
      $app->permission_internal = false;
      $app->save();

      $response = $this->call('GET', '/api/registration/by-email/'.$email, ['public' => $app->public, 'private' => $app->private]);
      $this->assertValidOkApiResponse($response);

      $apiReg = json_decode($response->getContent())->latest_registration;

      $this->assertNotNull($apiReg,
          'Latest registration was null.');
      $this->assertEquals($reg->first_name, $apiReg->first_name,
          'Registration first name was not equal to what is on record.');
      $this->assertEquals($reg->last_name, $apiReg->last_name,
          'Registration last name was not equal to what is on record.');
      $this->assertEquals($reg->email, $apiReg->email,
          'Registration email was not equal to what is on record.');

      $app->delete();
      $reg->delete();
    }
}

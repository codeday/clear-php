<?php
namespace CodeDay\Clear\Tests;

class ApiTestCase extends TestCase {

    public function assertValidApiResponse(\Symfony\Component\HttpFoundation\Response $response)
    {
        $this->assertNotNull($response->headers->get('Content-type'),
            'API Response had no content-type.');

        $this->assertContains('text/javascript', $response->headers->get('Content-type'),
            'API Response had invalid content-type.');

        $this->assertJson($response->getContent(),
            'API Response was not valid JSON.');
    }

    public function assertValidOkApiResponse(\Symfony\Component\HttpFoundation\Response $response)
    {
        $this->assertEquals(200, $response->getStatusCode(),
            "API reported an error:\n\n".$response->getContent());

        $this->assertNotNull($response->headers->get('Content-type'),
            'API Response had no content-type.');

        $this->assertContains('text/javascript', $response->headers->get('Content-type'),
            'API Response had invalid content-type.');

        $this->assertJson($response->getContent(),
            'API Response was not valid JSON.');
    }
}
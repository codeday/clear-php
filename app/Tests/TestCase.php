<?php
namespace CodeDay\Clear\Tests;

class TestCase extends \Illuminate\Foundation\Testing\TestCase {

    public function createApplication()
    {
        $unitTesting = true;
        $testEnvironment = 'phpunit';

        return require __DIR__.'/../../bootstrap/start.php';
    }
}
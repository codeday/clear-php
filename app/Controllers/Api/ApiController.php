<?php
namespace CodeDay\Clear\Controllers\Api;

abstract class ApiController extends \Controller {
    protected $permissions = ['internal'];

    public function __construct()
    {
        \App::after(function($request, $response)
        {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', '*');
            $response->headers->set('Content-type', 'text/javascript');
        });
    }
} 
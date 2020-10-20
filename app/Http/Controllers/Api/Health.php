<?php
namespace CodeDay\Clear\Http\Controllers\Api;

class Health extends ApiController {
    protected $requiresApplication = false;
    public function getIndex()
    {
        return \Response::make('OK', 200);
    }
}

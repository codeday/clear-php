<?php

namespace CodeDay\Clear\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class ShareSessionErrors extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function handle($request, \Closure $next)
    {
        \View::share('error', \Session::get('error'));
        \View::share('status_message', \Session::get('status_message'));
        return parent::handle($request, $next);
    }
}

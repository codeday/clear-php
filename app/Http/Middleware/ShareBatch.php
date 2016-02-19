<?php

namespace CodeDay\Clear\Http\Middleware;

use CodeDay\Clear\Models;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class ShareBatch extends BaseVerifier
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
        \View::share('loaded_batch', Models\Batch::Loaded());
        \View::share('managed_batch', Models\Batch::Managed());
        return parent::handle($request, $next);
    }
}

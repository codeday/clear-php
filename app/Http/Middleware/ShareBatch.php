<?php

namespace CodeDay\Clear\Http\Middleware;

use CodeDay\Clear\Models;

class ShareBatch
{
    public function handle($request, \Closure $next)
    {
        if (Models\User::is_logged_in()) {
            \View::share('loaded_batch', Models\Batch::Loaded());
            \View::share('managed_batch', Models\Batch::Managed());
        }
        return $next($request);
    }
}

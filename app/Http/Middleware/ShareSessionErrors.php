<?php

namespace CodeDay\Clear\Http\Middleware;

class ShareSessionErrors
{
    public function handle($request, \Closure $next)
    {
        
        \View::share('error', \Session::get('error'));
        \View::share('status_message', \Session::get('status_message'));
        return $next($request);
    }
}

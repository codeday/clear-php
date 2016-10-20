<?php

namespace CodeDay\Clear\Http\Middleware;

class ShareRaygunApiKey
{
    public function handle($request, \Closure $next)
    {
        \View::share('raygun_api_Key', \Config::get('raygun.api_key'));
        return $next($request);
    }
}

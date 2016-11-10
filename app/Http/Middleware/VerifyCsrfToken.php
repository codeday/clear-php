<?php

namespace CodeDay\Clear\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
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
        $csrf = csrf_token();
        \View::share('csrf_token', $csrf);
        \View::share('csrf', '<input type="hidden" name="_token" value="'.$csrf.'" />');

        $response = parent::handle($request, $next);

        $csp = "default-src 'self'; script-src 'unsafe-eval' 'unsafe-inline' 'self' https://*.googleapis.com https://*.stripe.com https://cdnjs.cloudflare.com https://*.filepicker.io https://*.typekit.net"
        . " http://*.filepicker.io http://code.jquery.com https://code.jquery.com https://*.gstatic.com; object-src 'self'; style-src 'self' 'unsafe-inline'"
        . " https://*.googleapis.com https://*.gstatic.com https://*.typekit.net; img-src 'unsafe-inline' *; media-src *; frame-src 'self' https://*.stripe.com https://*.filepicker.io https://*.legalesign.com;"
        . " font-src 'self' 'unsafe-inline' https://*.googleapis.com https://*.gstatic.com https://*.typekit.net; connect-src *;";

        if (\Request::server("HTTP_HOST") === 'clear.codeday.org') {
            $response->headers->set('Strict-Transport-Security', '2,592,000');
        }

        $response->headers->set('X-Frame-Options', 'deny');
        $response->headers->set('Frame-Options', 'deny');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        //$response->headers->set('Content-Security-Policy', $csp);
        //$response->headers->set('X-Content-Security-Policy', $csp);
        //$response->headers->set('X-WebKit-CSP', $csp);

        return $response;
    }
}

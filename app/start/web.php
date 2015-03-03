<?php

// XSS, CSRF, etc protection
\App::after(function($request, $response)
{
    $csp = "default-src 'self'; script-src 'unsafe-eval' 'unsafe-inline' 'self' https://*.googleapis.com https://cdnjs.cloudflare.com"
        . " http://code.jquery.com https://code.jquery.com https://*.gstatic.com; object-src 'self'; style-src 'self' 'unsafe-inline'"
        . " https://*.googleapis.com https://*.gstatic.com; img-src *; media-src *; frame-src 'self';"
        . " font-src 'self' https://*.googleapis.com https://*.gstatic.com; connect-src *";

    if (\Request::server("HTTP_HOST") === 'clear.codeday.org') {
        $response->headers->set('Strict-Transport-Security', '2,592,000');
    }

    $response->headers->set('X-Frame-Options', 'deny');
    $response->headers->set('Frame-Options', 'deny');
    $response->headers->set('X-XSS-Protection', '1; mode=block');
    $response->headers->set('X-Content-Type-Options', 'nosniff');
    $response->headers->set('Content-Security-Policy', $csp);
    $response->headers->set('X-Content-Security-Policy', $csp);
    $response->headers->set('X-WebKit-CSP', $csp);
});
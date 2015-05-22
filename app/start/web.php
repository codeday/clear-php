<?php

// XSS, CSRF, etc protection
\App::after(function($request, $response)
{
    $csp = "default-src 'self'; script-src 'unsafe-eval' 'unsafe-inline' 'self' https://*.googleapis.com https://*.stripe.com https://cdnjs.cloudflare.com https://*.filepicker.io"
        . " http://*.filepicker.io http://code.jquery.com https://code.jquery.com https://*.gstatic.com; object-src 'self'; style-src 'self' 'unsafe-inline'"
        . " https://*.googleapis.com https://*.gstatic.com; img-src *; media-src *; frame-src 'self' https://*.stripe.com https://*.filepicker.io;"
        . " font-src 'self' https://*.googleapis.com https://*.gstatic.com; connect-src *;";

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

\Route::filter('csrf', function(){
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { // Is it an AJAX request (these
                                                                                    // should be protected by default)
        return;
    } else if (\Session::token() != \Input::get('_token')) {
        \Session::regenerateToken();
        throw new \Illuminate\Session\TokenMismatchException;
    } else {
        \Session::regenerateToken();
    }
});
App::error(function(\Illuminate\Session\TokenMismatchException $exception)
{
    return (new \Illuminate\Http\Response(401))
        ->setContent(
            "# CSRF Token Mismatch\n\nIf you were on Clear, the page may have been idle too long. Try returning to"
            . " the previous page, refreshing, and trying your request again.\n\nIf you were NOT on Clear, it's likely"
            . " you were the subject of an attempted attack. You should report what page you came from."
        )
        ->header('Content-type', 'text/plain');
});

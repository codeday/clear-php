<?php

namespace CodeDay\Clear\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \CodeDay\Clear\Http\Middleware\ShareSessionErrors::class,
        \CodeDay\Clear\Http\Middleware\ShareBatch::class
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'web' => \CodeDay\Clear\Http\Middleware\VerifyCsrfToken::class,
        'api' => \CodeDay\Clear\Http\Middleware\ApiResponse::class
    ];
}

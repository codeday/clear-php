<?php

namespace CodeDay\Clear\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (!\Config::get('app.debug')) {
            $raygun = new \Raygun4php\RaygunClient(\Config::get("raygun.api_key"));
            $raygun->SetVersion(Services\GitRepository::getVersion());

            try{
                if(Models\User::me()){
                    $user = Models\User::me();
                    $raygun->SetUser($user->username, $user->first_name, $user->name, $user->email, false);
                }
            }
            catch(Exception $e2){
                // an exception while reporting an exception...
                $raygun->SendException($e2);
            }

            $raygun->SendException($e);
        }
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }
}

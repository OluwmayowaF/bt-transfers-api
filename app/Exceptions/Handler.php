<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
            return response()->json(['error' => $e],400);
        });

        $this->renderable(function(Throwable $e) {
            return $this->handleException($e);
        });
    }     

    public function handleException(Throwable $exception)
    {
        if($exception instanceof RouteNotFoundException) {
           return response()->json([
               'status' => false,
               'error' =>  $exception->getMessage(),
               'data' => null
           ], 404);
        }

        if($exception instanceof AuthenticationException) {
            return response()->json([
                'status' => false,
                'error' => 'Authentication error. Token required.',
                'data' => null

            ], 401);
         }

         if($exception instanceof ValidationException) {
            return response()->json([
                'status' => false,
                'error' => $exception->errors(),
                'data' => null
            ], 400);
         }

        else{
            return response()->json([
                'status' => false,
                'error' => $exception->getMessage(),
                'data' => null
            ], 500);
        }
    }
}

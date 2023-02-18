<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
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
        $this->renderable(function (JWTException $e, $request) {
            return response()->json([
                'code' => 417,
                'status' => false,
                'msg' => $e->getMessage(),
                'error' => 1
            ], 417);
        });

        $this->renderable(function (HttpException $exception, $request) {
            return response()->json([
                'code' => $exception->getstatusCode(),
                'status' => false,
                'msg' => $exception->getMessage() ? $exception->getMessage() : $exception->getFile(),
                'error' => 1,
                'error_detail' => [
                    'code' => $exception->getStatusCode(),
                    'headers' => $exception->getHeaders(),
                    'line' => $exception->getLine(),
                ]
            ], $exception->getstatusCode());
        });

        $this->renderable(function (NotFoundHttpException $exception, $request) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1
            ], 404);
        });

        $this->renderable(function (Exception $exception, $request) {
            return response()->json([
                'code' => $exception->getMessage() == 'Unauthenticated.' ? 401 : 500,
                'status' => false,
                'msg' => $exception->getMessage(),
                'error' => 1
            ], $exception->getMessage() == 'Unauthenticated.' ? 401 : 500);
        });
    }
}

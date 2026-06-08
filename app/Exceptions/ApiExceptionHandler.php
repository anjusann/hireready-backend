<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function handles(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    public static function render(Throwable $exception, Request $request): mixed
    {
        if (! self::handles($request)) {
            return null;
        }

        return match (true) {
            $exception instanceof ValidationException => ApiResponse::error(
                'Validation failed.',
                422,
                $exception->errors(),
            ),
            $exception instanceof AuthenticationException => ApiResponse::error(
                'Unauthenticated.',
                401,
            ),
            $exception instanceof AuthorizationException => ApiResponse::error(
                'Forbidden.',
                403,
            ),
            $exception instanceof ModelNotFoundException => ApiResponse::error(
                'Resource not found.',
                404,
            ),
            $exception instanceof NotFoundHttpException => ApiResponse::error(
                'Endpoint not found.',
                404,
            ),
            $exception instanceof MethodNotAllowedHttpException => ApiResponse::error(
                'Method not allowed.',
                405,
            ),
            $exception instanceof ThrottleRequestsException => ApiResponse::error(
                'Too many requests. Please try again later.',
                429,
            ),
            $exception instanceof HttpException => ApiResponse::error(
                $exception->getMessage() ?: 'HTTP error.',
                $exception->getStatusCode(),
            ),
            default => ApiResponse::error(
                config('app.debug') ? $exception->getMessage() : 'Internal server error.',
                500,
            ),
        };
    }
}

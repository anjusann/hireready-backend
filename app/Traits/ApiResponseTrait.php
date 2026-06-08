<?php

namespace App\Traits;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = [],
    ): JsonResponse {
        return ApiResponse::success($data, $message, $statusCode, $meta);
    }

    protected function errorResponse(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null,
        array $meta = [],
    ): JsonResponse {
        return ApiResponse::error($message, $statusCode, $errors, $meta);
    }
}

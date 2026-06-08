<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::transformData($data),
            'errors' => null,
            'meta' => (object) self::resolveMeta($data, $meta),
        ], $statusCode);
    }

    public static function error(
        string $message = 'Error',
        int $statusCode = 400,
        mixed $errors = null,
        array $meta = [],
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
            'meta' => (object) $meta,
        ], $statusCode);
    }

    protected static function transformData(mixed $data): mixed
    {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->resolve();
        }

        return $data;
    }

    protected static function resolveMeta(mixed $data, array $meta): array
    {
        if ($data instanceof AbstractPaginator) {
            return array_merge($meta, [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
            ]);
        }

        return $meta;
    }
}

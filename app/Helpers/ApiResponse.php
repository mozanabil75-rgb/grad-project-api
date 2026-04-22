<?php

namespace App\Helpers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiResponse
{
    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => self::normalizeData($data),
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => self::normalizeErrors($errors),
        ], $code);
    }

    private static function normalizeData(mixed $data): mixed
    {
        if ($data === null) {
            return new \stdClass();
        }

        if ($data instanceof JsonResource) {
            return $data->resolve(request());
        }

        if ($data instanceof ResourceCollection || $data instanceof AnonymousResourceCollection) {
            return $data->resolve(request());
        }

        if (is_array($data)) {
            return array_map(static function (mixed $item): mixed {
                if ($item instanceof JsonResource) {
                    return $item->resolve(request());
                }

                return $item;
            }, $data);
        }

        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        return $data;
    }

    private static function normalizeErrors(mixed $errors): mixed
    {
        if ($errors === null) {
            return new \stdClass();
        }

        return $errors;
    }
}

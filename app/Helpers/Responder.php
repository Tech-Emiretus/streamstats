<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class Responder
{
    public static function success($data = [], string $message = '', $code = 200): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    public static function error($data = [], string $error = '', $code = 400): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $error, 'data' => $data], $code);
    }
}

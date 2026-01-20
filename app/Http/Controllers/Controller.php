<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    public function setResponse(
        string $message,
        int $code = 200,
        array $headers = [],
        array $data = []
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code, $headers);
    }
}

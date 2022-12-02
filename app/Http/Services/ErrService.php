<?php

namespace App\Http\Services;
use Illuminate\Http\Request;

class ErrService
{
    public function errResponse($exception): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $exception->getMessage()
            ]
        ], $exception->getCode());
    }
}

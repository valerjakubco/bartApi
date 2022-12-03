<?php

namespace App\Http\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ErrService
{
    public function errResponse($exception)
    {
        return response()->json([
        'error' => [
            'message' => $exception->getMessage()
        ]
    ], $exception->getCode());
    }
}

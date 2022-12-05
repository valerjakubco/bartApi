<?php

namespace App\Http\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ErrService
{
    public function errResponse($message, $code)
    {
        return response()->json([
        'error' => [
            'message' => $message
        ]
    ], $code);
    }
}

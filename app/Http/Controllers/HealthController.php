<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'app' => 'running',
        ], 200, [
            'Cache-Control' => 'no-store',
        ]);
    }
}

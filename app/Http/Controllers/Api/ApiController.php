<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Send a successful JSON response matching the mobile frontend's ApiClient expectations.
     */
    protected function sendSuccess(mixed $data, int $statusCode = 200): JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Send an error JSON response matching firesight-api's sendError shape: { "message": "..." }.
     */
    protected function sendError(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json(['message' => $message], $statusCode);
    }
}

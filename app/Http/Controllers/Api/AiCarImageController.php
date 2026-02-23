<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Stub for AI car image generation (driver app).
 * GET or POST /api/ai/generate-car-image and /api/v1/ai/generate-car-image.
 * Returns stable JSON; no fatal errors on missing params.
 */
class AiCarImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        // Optional: make, model, color — accepted but not required; stub does not use them
        return response()->json([
            'ok' => true,
            'image_url' => null,
            'message' => 'Not implemented yet',
        ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiCarImageJob;
use App\Models\AiCarImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AiCarImageController extends Controller
{
    /**
     * POST/GET /api/ai/generate-car-image — requires auth. Queues job, returns job_id.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'make' => 'nullable|string|max:50',
            'model' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        $id = (string) Str::uuid();
        AiCarImage::create([
            'id' => $id,
            'user_id' => $request->user()?->getKey(),
            'make' => $validated['make'] ?? null,
            'model' => $validated['model'] ?? null,
            'color' => $validated['color'] ?? null,
            'status' => AiCarImage::STATUS_QUEUED,
        ]);

        GenerateAiCarImageJob::dispatch($id);

        return response()->json([
            'ok' => true,
            'job_id' => $id,
            'status' => 'queued',
        ], 200);
    }

    /**
     * GET /api/ai/generate-car-image/status?job_id=<uuid> — requires auth, own job only.
     */
    public function status(Request $request): JsonResponse
    {
        $jobId = $request->query('job_id');
        if (!$jobId || !is_string($jobId)) {
            return response()->json([
                'ok' => false,
                'status' => 'failed',
                'image_url' => null,
                'message' => 'Missing job_id',
            ], 400);
        }

        $record = AiCarImage::where('id', $jobId)
            ->where('user_id', $request->user()?->getKey())
            ->first();

        if (!$record) {
            return response()->json([
                'ok' => false,
                'status' => 'failed',
                'image_url' => null,
                'message' => 'Job not found',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'status' => $record->status,
            'image_url' => $record->image_url,
            'message' => $record->error_message,
        ], 200);
    }
}

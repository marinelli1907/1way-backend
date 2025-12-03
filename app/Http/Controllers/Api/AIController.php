<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    public function handleUno(Request $request)
    {
        $input = $request->validate([
            'message' => 'required|string',
            'context' => 'nullable|array',
        ]);

        $system = <<<TXT
You are Uno, the assistant inside the 1Way LifeBook app.
You help users plan, organize, book rides, and manage events.
Keep responses friendly and clear.
TXT;

        $key = env('OPENAI_API_KEY');
        if (!$key) {
            return ['reply' => 'Uno is not configured yet (missing OPENAI_API_KEY).'];
        }

        $response = Http::withToken($key)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $input['message']],
                ]
            ]);

        if (!$response->ok()) {
            return ['reply' => 'Uno encountered an error talking to the AI backend.'];
        }

        return [
            'reply' => $response->json()['choices'][0]['message']['content'] ?? '...'
        ];
    }
}

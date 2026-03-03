<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XaiClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.xai.base_url', 'https://api.x.ai/v1'), '/');
        $this->apiKey  = config('services.xai.key', '');
        $this->model   = config('services.xai.model', 'grok-2');
    }

    /**
     * Send a chat completion request to xAI/Grok.
     *
     * @param  array  $messages  [{role, content}, ...]
     * @param  array  $options   Optional overrides (model, temperature, max_tokens, etc.)
     * @return array  Decoded JSON response body
     * @throws AiException
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            throw new AiException('XAI_API_KEY is not configured', null);
        }

        $payload = array_merge([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => 0.2,
            'max_tokens'  => 1024,
        ], $options);

        try {
            $response = Http::timeout(15)
                ->retry(2, 250, fn ($e, $request) => $e instanceof \Illuminate\Http\Client\ConnectionException
                    || ($e instanceof \Illuminate\Http\Client\RequestException && in_array($e->response->status(), [429, 500, 502, 503])))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('pricing_ai')->error('XaiClient connection timeout', [
                'error' => $e->getMessage(),
            ]);
            throw new AiException('AI provider connection timeout', null, $e);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::channel('pricing_ai')->error('XaiClient request failed', [
                'status' => $e->response?->status(),
            ]);
            throw new AiException(
                'AI provider error (HTTP ' . ($e->response?->status() ?? '?') . ')',
                $e->response?->status(),
                $e
            );
        }

        if (!$response->successful()) {
            Log::channel('pricing_ai')->error('XaiClient non-200 response', [
                'status' => $response->status(),
            ]);
            throw new AiException(
                'AI provider returned HTTP ' . $response->status(),
                $response->status()
            );
        }

        $body = $response->json();
        if (!is_array($body)) {
            throw new AiException('AI provider returned non-JSON response');
        }

        return $body;
    }

    /**
     * Extract the first message content string from a chat response.
     */
    public static function extractContent(array $response): ?string
    {
        return $response['choices'][0]['message']['content'] ?? null;
    }
}

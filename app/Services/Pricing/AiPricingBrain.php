<?php

namespace App\Services\Pricing;

use App\Services\Ai\AiException;
use App\Services\Ai\XaiClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiPricingBrain
{
    protected XaiClient $ai;

    // Guardrail defaults
    protected int $minFareCents = 900;
    protected int $maxFareCents = 25000;
    protected float $maxDeviationPercent = 0.40;

    // Allowed knob ranges [min, max]
    protected const KNOB_RANGES = [
        'demand_multiplier'   => [0.7, 2.5],
        'supply_multiplier'   => [0.7, 2.5],
        'zone_multiplier'     => [0.8, 1.8],
        'time_multiplier'     => [0.8, 2.0],
        'risk_multiplier'     => [0.8, 2.0],
        'discount_multiplier' => [0.5, 1.0],
    ];

    protected const VALID_DISPATCH = ['nearest', 'best_rated', 'balanced'];

    public function __construct(XaiClient $ai)
    {
        $this->ai = $ai;
    }

    /**
     * @param array $input Keys: distance_meters, duration_seconds, base_fare_cents,
     *   deterministic_fare_cents, zone_id, zone_name, requested_time, demand_index, supply_index
     * @param array $opts  Keys: min_fare_cents, max_fare_cents, allow_override
     */
    public function evaluate(array $input, array $opts = []): array
    {
        $requestId = Str::uuid()->toString();
        $log = Log::channel('pricing_ai');

        $minFare = $opts['min_fare_cents'] ?? $this->minFareCents;
        $maxFare = $opts['max_fare_cents'] ?? $this->maxFareCents;
        $allowOverride = $opts['allow_override'] ?? false;

        $deterministicFare = (int) ($input['deterministic_fare_cents'] ?? 0);
        $baseFare = (int) ($input['base_fare_cents'] ?? 0);

        $log->info('ai_pricing_request', [
            'request_id'           => $requestId,
            'distance_meters'      => $input['distance_meters'] ?? 0,
            'duration_seconds'     => $input['duration_seconds'] ?? 0,
            'base_fare_cents'      => $baseFare,
            'deterministic_fare'   => $deterministicFare,
            'zone_id'              => $input['zone_id'] ?? null,
        ]);

        try {
            $aiResult = $this->callAi($input, $requestId);
        } catch (\Throwable $e) {
            $log->warning('ai_pricing_fallback', [
                'request_id' => $requestId,
                'reason'     => 'ai_call_failed',
                'error'      => $e->getMessage(),
            ]);
            return $this->fallbackResult($deterministicFare, $minFare, $maxFare, $requestId, 'AI call failed: ' . $e->getMessage());
        }

        // Validate + clamp multipliers
        $multipliers = $this->extractAndClampMultipliers($aiResult);
        $dispatchBias = $this->extractDispatchBias($aiResult);
        $confidence = $this->clampFloat($aiResult['confidence'] ?? 0.0, 0.0, 1.0);
        $reasons = is_array($aiResult['reasons'] ?? null) ? array_slice($aiResult['reasons'], 0, 10) : [];
        $reasons = array_map(fn($r) => is_string($r) ? Str::limit($r, 200) : '', $reasons);

        // Compute final fare
        $combinedMultiplier = 1.0;
        foreach ($multipliers as $val) {
            $combinedMultiplier *= $val;
        }

        $rawAiFare = (int) round($deterministicFare * $combinedMultiplier);

        // Deviation guardrail
        $maxAllowed = (int) round($deterministicFare * (1 + $this->maxDeviationPercent));
        $minAllowed = (int) round($deterministicFare * (1 - $this->maxDeviationPercent));

        $finalFare = $rawAiFare;

        if ($rawAiFare > $maxAllowed) {
            if ($allowOverride && $confidence >= 0.8) {
                $finalFare = $rawAiFare;
            } else {
                $finalFare = $maxAllowed;
            }
        } elseif ($rawAiFare < $minAllowed) {
            $finalFare = max($minAllowed, $minFare);
        }

        $finalFare = max($minFare, min($maxFare, $finalFare));

        $fallback = false;
        $log->info('ai_pricing_result', [
            'request_id'         => $requestId,
            'deterministic_fare' => $deterministicFare,
            'raw_ai_fare'        => $rawAiFare,
            'final_fare'         => $finalFare,
            'combined_multiplier' => round($combinedMultiplier, 4),
            'multipliers'        => $multipliers,
            'dispatch_bias'      => $dispatchBias,
            'confidence'         => $confidence,
            'fallback'           => false,
        ]);

        return [
            'final_fare_cents'         => $finalFare,
            'deterministic_fare_cents' => $deterministicFare,
            'multipliers'              => $multipliers,
            'dispatch_bias'            => $dispatchBias,
            'confidence'               => $confidence,
            'reasons'                  => $reasons,
            'fallback'                 => false,
            'request_id'               => $requestId,
        ];
    }

    // ─── AI call ─────────────────────────────────────────────────────────

    protected function callAi(array $input, string $requestId): array
    {
        $requestedTime = $input['requested_time'] ?? now()->toIso8601String();
        $dayOfWeek = date('l', strtotime($requestedTime));

        $schema = <<<'JSON'
{
  "demand_multiplier": number,
  "supply_multiplier": number,
  "zone_multiplier": number,
  "time_multiplier": number,
  "risk_multiplier": number,
  "discount_multiplier": number,
  "dispatch_bias": "nearest|best_rated|balanced",
  "confidence": number,
  "reasons": [string]
}
JSON;

        $userPrompt = sprintf(
            "Analyze this ride and return pricing multipliers.\n\n"
            . "distance_meters: %d\n"
            . "duration_seconds: %d\n"
            . "requested_time_local: %s\n"
            . "day_of_week: %s\n"
            . "zone_id: %s\n"
            . "zone_name: %s\n"
            . "demand_index: %.2f\n"
            . "supply_index: %.2f\n"
            . "base_fare_cents: %d\n"
            . "deterministic_fare_cents: %d\n\n"
            . "Return ONLY this JSON schema:\n%s",
            $input['distance_meters'] ?? 0,
            $input['duration_seconds'] ?? 0,
            $requestedTime,
            $dayOfWeek,
            $input['zone_id'] ?? 'unknown',
            $input['zone_name'] ?? 'unknown',
            $input['demand_index'] ?? 1.0,
            $input['supply_index'] ?? 1.0,
            $input['base_fare_cents'] ?? 0,
            $input['deterministic_fare_cents'] ?? 0,
            $schema
        );

        $messages = [
            ['role' => 'system', 'content' => 'You are a ride-share pricing engine. Return ONLY valid JSON. No markdown. No prose. No explanation.'],
            ['role' => 'user', 'content' => $userPrompt],
        ];

        $response = $this->ai->chat($messages, ['temperature' => 0.1]);
        $content = XaiClient::extractContent($response);

        if (!$content) {
            throw new AiException('Empty AI response content');
        }

        // Strip markdown fences if the model wraps in ```json
        $content = trim($content);
        if (str_starts_with($content, '```')) {
            $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);
        }

        $parsed = json_decode($content, true);
        if (!is_array($parsed)) {
            throw new AiException('AI returned invalid JSON: parse error');
        }

        return $parsed;
    }

    // ─── Multiplier extraction + clamping ────────────────────────────────

    protected function extractAndClampMultipliers(array $data): array
    {
        $result = [];
        foreach (self::KNOB_RANGES as $knob => [$min, $max]) {
            $val = $data[$knob] ?? 1.0;
            if (!is_numeric($val)) {
                $val = 1.0;
            }
            $result[$knob] = $this->clampFloat((float) $val, $min, $max);
        }
        return $result;
    }

    protected function extractDispatchBias(array $data): string
    {
        $bias = $data['dispatch_bias'] ?? 'nearest';
        return in_array($bias, self::VALID_DISPATCH, true) ? $bias : 'nearest';
    }

    protected function clampFloat(float $val, float $min, float $max): float
    {
        return round(max($min, min($max, $val)), 4);
    }

    // ─── Fallback ────────────────────────────────────────────────────────

    protected function fallbackResult(int $deterministicFare, int $minFare, int $maxFare, string $requestId, string $reason): array
    {
        $finalFare = max($minFare, min($maxFare, $deterministicFare));

        Log::channel('pricing_ai')->info('ai_pricing_result', [
            'request_id'         => $requestId,
            'deterministic_fare' => $deterministicFare,
            'final_fare'         => $finalFare,
            'fallback'           => true,
            'fallback_reason'    => $reason,
        ]);

        $defaultMultipliers = [];
        foreach (self::KNOB_RANGES as $knob => $_) {
            $defaultMultipliers[$knob] = 1.0;
        }
        $defaultMultipliers['discount_multiplier'] = 1.0;

        return [
            'final_fare_cents'         => $finalFare,
            'deterministic_fare_cents' => $deterministicFare,
            'multipliers'              => $defaultMultipliers,
            'dispatch_bias'            => 'nearest',
            'confidence'               => 0.0,
            'reasons'                  => ['Fallback to deterministic pricing: ' . $reason],
            'fallback'                 => true,
            'request_id'               => $requestId,
        ];
    }
}

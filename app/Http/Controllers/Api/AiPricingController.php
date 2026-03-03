<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsException;
use App\Services\GoogleMapsService;
use App\Services\Pricing\AiPricingBrain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\ZoneManagement\Entities\ServiceZone;

class AiPricingController extends Controller
{
    protected GoogleMapsService $maps;
    protected AiPricingBrain $brain;

    public function __construct(GoogleMapsService $maps, AiPricingBrain $brain)
    {
        $this->maps = $maps;
        $this->brain = $brain;
    }

    public function quote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat'     => 'required|numeric|between:-90,90',
            'pickup_lng'     => 'required|numeric|between:-180,180',
            'dropoff_lat'    => 'required|numeric|between:-90,90',
            'dropoff_lng'    => 'required|numeric|between:-180,180',
            'requested_time' => 'nullable|date',
            'ride_type'      => 'nullable|string|in:standard,premium,xl',
            'zone_id'        => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pickupLat  = (float) $request->pickup_lat;
        $pickupLng  = (float) $request->pickup_lng;
        $dropoffLat = (float) $request->dropoff_lat;
        $dropoffLng = (float) $request->dropoff_lng;

        // Zone lookup (enforcement + pricing rules)
        $zone = null;
        try {
            $geoService = app(\Modules\ZoneManagement\Service\GeoZoneService::class);
            $zone = $geoService->findZoneForPoint($pickupLat, $pickupLng);
        } catch (\Throwable $e) {
            Log::warning('[AiPricingController] Zone lookup failed', ['error' => $e->getMessage()]);
        }

        if (!$zone && $this->isZoneEnforced()) {
            return response()->json([
                'success' => false,
                'message' => 'Outside service area. We do not currently serve this pickup location.',
            ], 422);
        }

        // Distance Matrix
        $origin = "{$pickupLat},{$pickupLng}";
        $destination = "{$dropoffLat},{$dropoffLng}";

        try {
            $dm = $this->maps->distanceMatrix($origin, $destination);
        } catch (GoogleMapsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to calculate route: ' . $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('[AiPricingController] Distance Matrix error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Route calculation failed.'], 500);
        }

        if (($dm['raw_status'] ?? '') !== 'OK') {
            return response()->json([
                'success' => false,
                'message' => $dm['raw_error_message'] ?? 'No route found.',
            ], 422);
        }

        $distanceMeters  = $dm['distance_meters'];
        $durationSeconds = $dm['duration_in_traffic_seconds'] ?: $dm['duration_seconds'];

        // Deterministic pricing using zone rules
        $detPricing = PricingController::computeDeterministicFare($distanceMeters, $durationSeconds, $zone);
        $deterministicFare = $detPricing['final_fare_cents'];

        $zoneRules = $zone ? $zone->effectivePricingRules() : ServiceZone::DEFAULT_PRICING_RULES;

        // AI Brain — pass zone constraints
        try {
            $aiResult = $this->brain->evaluate([
                'distance_meters'          => $distanceMeters,
                'duration_seconds'         => $durationSeconds,
                'base_fare_cents'          => $detPricing['base_fee_cents'],
                'deterministic_fare_cents' => $deterministicFare,
                'zone_id'                  => $zone?->id,
                'zone_name'                => $zone?->name,
                'requested_time'           => $request->requested_time ?? now()->toIso8601String(),
                'demand_index'             => 1.0,
                'supply_index'             => 1.0,
            ], [
                'min_fare_cents' => (int) $zoneRules['min_fare_cents'],
                'max_fare_cents' => (int) $zoneRules['max_fare_cents'],
            ]);
        } catch (\Throwable $e) {
            Log::error('[AiPricingController] Brain evaluate failed', ['error' => $e->getMessage()]);
            $aiResult = [
                'final_fare_cents'         => $deterministicFare,
                'deterministic_fare_cents' => $deterministicFare,
                'multipliers'              => [],
                'dispatch_bias'            => 'nearest',
                'confidence'               => 0.0,
                'reasons'                  => ['Emergency fallback'],
                'fallback'                 => true,
            ];
        }

        // Enforce zone surge cap on AI result
        $surgeCap = (float) $zoneRules['surge_cap_multiplier'];
        $surgedMax = (int) round($deterministicFare * $surgeCap);
        $aiFare = $aiResult['final_fare_cents'];
        $capped = false;
        if ($aiFare > $surgedMax) {
            $aiFare = $surgedMax;
            $capped = true;
        }
        $aiFare = max((int) $zoneRules['min_fare_cents'], min((int) $zoneRules['max_fare_cents'], $aiFare));

        return response()->json([
            'success'                  => true,
            'base_fare_cents'          => $detPricing['base_fee_cents'],
            'deterministic_fare_cents' => $deterministicFare,
            'final_fare_cents'         => $aiFare,
            'currency'                 => 'USD',
            'distance_meters'          => $distanceMeters,
            'duration_seconds'         => $durationSeconds,
            'dispatch_bias'            => $aiResult['dispatch_bias'],
            'multipliers'              => $aiResult['multipliers'],
            'confidence'               => $aiResult['confidence'],
            'reasons'                  => $aiResult['reasons'],
            'fallback'                 => $aiResult['fallback'],
            'capped'                   => $capped,
            'zone_id'                  => $zone?->id,
            'zone_name'                => $zone?->name,
            'source'                   => $aiResult['fallback'] ? 'deterministic' : 'ai',
        ]);
    }

    protected function isZoneEnforced(): bool
    {
        return filter_var(env('ZONES_ENFORCED', false), FILTER_VALIDATE_BOOLEAN);
    }
}

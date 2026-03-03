<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsException;
use App\Services\GoogleMapsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\ZoneManagement\Entities\ServiceZone;

class PricingController extends Controller
{
    protected GoogleMapsService $maps;

    public function __construct(GoogleMapsService $maps)
    {
        $this->maps = $maps;
    }

    public function quote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pickup_place_id'  => 'nullable|string|max:300',
            'dropoff_place_id' => 'nullable|string|max:300',
            'pickup_lat'       => 'nullable|numeric|between:-90,90',
            'pickup_lng'       => 'nullable|numeric|between:-180,180',
            'dropoff_lat'      => 'nullable|numeric|between:-90,90',
            'dropoff_lng'      => 'nullable|numeric|between:-180,180',
            'requested_time'   => 'nullable|date',
            'ride_type'        => 'nullable|string|in:standard,premium,xl',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $hasPlaceIds = $request->filled('pickup_place_id') && $request->filled('dropoff_place_id');
        $hasLatLng   = $request->filled('pickup_lat') && $request->filled('pickup_lng')
                    && $request->filled('dropoff_lat') && $request->filled('dropoff_lng');

        if (!$hasPlaceIds && !$hasLatLng) {
            return response()->json([
                'success' => false,
                'message' => 'Provide both pickup/dropoff place_ids OR both pickup/dropoff lat/lng pairs.',
            ], 422);
        }

        $pickupLat = $hasLatLng ? (float) $request->pickup_lat : null;
        $pickupLng = $hasLatLng ? (float) $request->pickup_lng : null;

        // Zone lookup (for enforcement + pricing rules)
        $zone = null;
        if ($pickupLat !== null) {
            try {
                $geoService = app(\Modules\ZoneManagement\Service\GeoZoneService::class);
                $zone = $geoService->findZoneForPoint($pickupLat, $pickupLng);
            } catch (\Throwable $e) {
                Log::warning('[PricingController] Zone lookup failed', ['error' => $e->getMessage()]);
            }

            if (!$zone && $this->isZoneEnforced()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outside service area. We do not currently serve this pickup location.',
                ], 422);
            }
        }

        $usedMode = $hasPlaceIds ? 'place_id' : 'latlng';
        $origin = GoogleMapsService::formatOrigin(
            $hasPlaceIds ? $request->pickup_place_id : null, $pickupLat, $pickupLng
        );
        $destination = GoogleMapsService::formatOrigin(
            $hasPlaceIds ? $request->dropoff_place_id : null,
            $hasLatLng ? (float) $request->dropoff_lat : null,
            $hasLatLng ? (float) $request->dropoff_lng : null
        );

        try {
            $dm = $this->maps->distanceMatrix($origin, $destination);
        } catch (GoogleMapsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not calculate route: ' . $e->getMessage(),
                'debug'   => ['google_status' => $e->getGoogleStatus()],
            ], 502);
        } catch (\Throwable $e) {
            Log::error('[PricingController] Distance Matrix error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Route calculation failed.'], 500);
        }

        if (($dm['raw_status'] ?? '') !== 'OK') {
            return response()->json([
                'success' => false,
                'message' => $dm['raw_error_message'] ?? 'No route found.',
                'debug'   => ['google_status' => $dm['raw_status']],
            ], 422);
        }

        $distanceMeters  = $dm['distance_meters'];
        $durationSeconds = $dm['duration_in_traffic_seconds'] ?: $dm['duration_seconds'];

        $pricing = self::computeDeterministicFare($distanceMeters, $durationSeconds, $zone);

        return response()->json([
            'success'          => true,
            'distance_meters'  => $distanceMeters,
            'duration_seconds' => $durationSeconds,
            'distance_miles'   => round($distanceMeters / 1609.34, 2),
            'duration_minutes' => round($durationSeconds / 60, 1),
            'pricing'          => $pricing,
            'zone_id'          => $zone?->id,
            'zone_name'        => $zone?->name,
            'source'           => 'deterministic',
            'debug'            => [
                'mode'          => 'driving',
                'used'          => $usedMode,
                'google_status' => $dm['raw_status'],
            ],
        ]);
    }

    /**
     * Compute deterministic fare using zone pricing rules (or global defaults).
     * Shared by both /api/pricing/quote and /api/pricing/ai-quote.
     */
    public static function computeDeterministicFare(int $distanceMeters, int $durationSeconds, ?ServiceZone $zone = null): array
    {
        $rules = $zone ? $zone->effectivePricingRules() : ServiceZone::DEFAULT_PRICING_RULES;

        $distanceMiles   = $distanceMeters / 1609.34;
        $durationMinutes = $durationSeconds / 60;

        $baseFee     = (int) $rules['base_fee_cents'];
        $bookingFee  = (int) $rules['booking_fee_cents'];
        $perMile     = (int) $rules['per_mile_cents'];
        $perMinute   = (int) $rules['per_minute_cents'];
        $airportFee  = (int) $rules['airport_fee_cents'];
        $minFare     = (int) $rules['min_fare_cents'];
        $maxFare     = (int) $rules['max_fare_cents'];
        $surgeCap    = (float) $rules['surge_cap_multiplier'];

        $variable  = ($distanceMiles * $perMile) + ($durationMinutes * $perMinute);
        $subtotal  = $baseFee + $bookingFee + $variable + $airportFee;
        $finalFare = max($minFare, min($maxFare, (int) round($subtotal)));

        return [
            'base_fee_cents'       => $baseFee,
            'booking_fee_cents'    => $bookingFee,
            'per_mile_cents'       => $perMile,
            'per_minute_cents'     => $perMinute,
            'airport_fee_cents'    => $airportFee,
            'surge_multiplier'     => 1.0,
            'surge_cap_multiplier' => $surgeCap,
            'capped'               => false,
            'final_fare_cents'     => $finalFare,
            'min_fare_cents'       => $minFare,
            'max_fare_cents'       => $maxFare,
        ];
    }

    protected function isZoneEnforced(): bool
    {
        return filter_var(env('ZONES_ENFORCED', false), FILTER_VALIDATE_BOOLEAN);
    }
}

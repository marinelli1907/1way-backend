<?php

namespace Modules\TripManagement\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Services\Flights\FlightStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\TripManagement\Entities\TripRequest;

class RefreshFlightController extends Controller
{
    public function __construct(
        private FlightStatusService $flightStatusService
    ) {}

    /**
     * POST driver/ride/{trip_request_id}/refresh-flight
     * Only assigned driver or admin can refresh.
     */
    public function refresh(string $trip_request_id): JsonResponse
    {
        $trip = TripRequest::find($trip_request_id);
        if (!$trip) {
            return response()->json(['message' => 'Ride not found'], 404);
        }

        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $isDriver = $trip->driver_id && (string) $trip->driver_id === (string) $user->id;
        $isAdmin = in_array($user->user_type ?? '', ['super-admin', 'admin', 'admin-employee'], true);
        if (!$isDriver && !$isAdmin) {
            return response()->json(['message' => 'Only the assigned driver or an admin can refresh flight status.'], 403);
        }

        if (empty($trip->flight_number) || empty($trip->flight_date)) {
            return response()->json(['message' => 'This ride has no flight information to refresh.'], 422);
        }

        $raw = strtoupper(preg_replace('/\s+/', '', $trip->flight_number));
        if (!preg_match('/^([A-Z]{2})(\d{1,4})$/', $raw, $m)) {
            return response()->json(['message' => 'Invalid flight number format on this ride.'], 422);
        }

        $carrier = $m[1];
        $flightNumber = $m[2];
        $date = $trip->flight_date instanceof \Carbon\Carbon
            ? $trip->flight_date->format('Y-m-d')
            : (\is_string($trip->flight_date) ? $trip->flight_date : null);
        if (!$date) {
            return response()->json(['message' => 'Flight date is missing.'], 422);
        }

        try {
            $normalized = $this->flightStatusService->getNormalizedStatus($carrier, $flightNumber, $date);
        } catch (\Throwable $e) {
            Log::channel('flight_api')->error('refresh_flight_error', [
                'trip_id' => $trip->id,
                'flight'  => $trip->flight_number,
                'date'   => $date,
                'error'  => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Unable to fetch flight status. Please try again later.',
            ], 502);
        }

        if ($normalized === null) {
            $trip->flight_status_cached = null;
            $trip->flight_status_checked_at = now();
            $trip->save();
            return response()->json([
                'message' => 'Flight not found.',
                'flight_status_cached' => null,
                'flight_status_checked_at' => $trip->flight_status_checked_at->toIso8601String(),
            ]);
        }

        $trip->flight_status_cached = $normalized;
        $trip->flight_status_checked_at = now();
        $trip->save();

        return response()->json([
            'message' => 'Flight status updated.',
            'flight_status_cached' => $normalized,
            'flight_status_checked_at' => $trip->flight_status_checked_at->toIso8601String(),
        ]);
    }
}

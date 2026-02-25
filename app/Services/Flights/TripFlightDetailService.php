<?php

namespace App\Services\Flights;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\TripManagement\Entities\TripFlightDetail;

class TripFlightDetailService
{
    public function __construct(private readonly FlightService $flightService)
    {
    }

    public function extractContext(array $payload): ?array
    {
        $mode = $payload['ride_airport_mode'] ?? null;
        $inputType = $payload['flight_input_type'] ?? null;

        if (! in_array($mode, ['airport_pickup', 'airport_dropoff'], true)) {
            return null;
        }
        if (! in_array($inputType, ['flight_number', 'reservation'], true)) {
            return null;
        }

        return [
            'ride_airport_mode' => $mode,
            'input_type' => $inputType,
            'flight_number' => isset($payload['flight_number']) ? strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $payload['flight_number'])) : null,
            'flight_date' => $payload['flight_date'] ?? null,
            'reservation_code' => $payload['reservation_code'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
        ];
    }

    public function attachToTrip(string $tripRequestId, array $context): TripFlightDetail
    {
        $base = [
            'trip_request_id' => $tripRequestId,
            'input_type' => $context['input_type'],
            'flight_number' => $context['flight_number'] ?? null,
            'flight_date' => $context['flight_date'] ?? null,
            'last_synced_at' => now(),
        ];

        if ($context['input_type'] === 'reservation') {
            return TripFlightDetail::query()->updateOrCreate(
                ['trip_request_id' => $tripRequestId],
                array_merge($base, [
                    'provider' => 'mock',
                    'verified' => false,
                    'status' => 'not_supported',
                    'raw' => [
                        'reservation_code' => $context['reservation_code'] ?? null,
                        'last_name' => $context['last_name'] ?? null,
                        'reason' => 'reservation_lookup_not_supported_in_mvp',
                    ],
                ])
            );
        }

        try {
            $flight = $this->flightService->lookupFlightNumber(
                (string) ($context['flight_number'] ?? ''),
                (string) ($context['flight_date'] ?? now()->toDateString()),
                (string) $context['ride_airport_mode'],
            );

            return TripFlightDetail::query()->updateOrCreate(
                ['trip_request_id' => $tripRequestId],
                array_merge($base, [
                    'provider' => $flight->provider,
                    'verified' => $flight->verified,
                    'status' => $flight->status,
                    'airline_code' => $flight->airlineCode,
                    'airline_name' => $flight->airlineName,
                    'dep_airport_iata' => $flight->depAirportIata,
                    'dep_airport_name' => $flight->depAirportName,
                    'arr_airport_iata' => $flight->arrAirportIata,
                    'arr_airport_name' => $flight->arrAirportName,
                    'sched_dep_at' => $flight->schedDepAt,
                    'sched_arr_at' => $flight->schedArrAt,
                    'est_dep_at' => $flight->estDepAt,
                    'est_arr_at' => $flight->estArrAt,
                    'terminal' => $flight->terminal,
                    'gate' => $flight->gate,
                    'baggage' => $flight->baggage,
                    'raw' => $flight->raw,
                ])
            );
        } catch (\Throwable $e) {
            Log::warning('Flight attach lookup failed', ['trip_id' => $tripRequestId, 'error' => $e->getMessage()]);

            return TripFlightDetail::query()->updateOrCreate(
                ['trip_request_id' => $tripRequestId],
                array_merge($base, [
                    'provider' => 'lookup_failed',
                    'verified' => false,
                    'status' => 'unverified',
                    'raw' => ['error' => $e->getMessage()],
                ])
            );
        }
    }

    public function formatForApi(?TripFlightDetail $detail): ?array
    {
        if (! $detail) {
            return null;
        }

        $recommended = null;
        if ($detail->sched_arr_at) {
            $recommended = Carbon::parse($detail->sched_arr_at, 'UTC')
                ->addMinutes((int) config('flight.default_airport_buffer_min', 25))
                ->toDateTimeString();
        }

        return [
            'provider' => $detail->provider,
            'verified' => (bool) $detail->verified,
            'input_type' => $detail->input_type,
            'flight_number' => $detail->flight_number,
            'flight_date' => optional($detail->flight_date)->format('Y-m-d'),
            'airline_code' => $detail->airline_code,
            'airline_name' => $detail->airline_name,
            'status' => $detail->status,
            'dep_airport_iata' => $detail->dep_airport_iata,
            'dep_airport_name' => $detail->dep_airport_name,
            'arr_airport_iata' => $detail->arr_airport_iata,
            'arr_airport_name' => $detail->arr_airport_name,
            'sched_dep_at' => optional($detail->sched_dep_at)->toDateTimeString(),
            'sched_arr_at' => optional($detail->sched_arr_at)->toDateTimeString(),
            'est_dep_at' => optional($detail->est_dep_at)->toDateTimeString(),
            'est_arr_at' => optional($detail->est_arr_at)->toDateTimeString(),
            'terminal' => $detail->terminal,
            'gate' => $detail->gate,
            'baggage' => $detail->baggage,
            'last_synced_at' => optional($detail->last_synced_at)->toDateTimeString(),
            'recommended_pickup_time' => $recommended,
        ];
    }
}

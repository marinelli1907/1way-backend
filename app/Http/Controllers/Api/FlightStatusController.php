<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Flights\AmadeusClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FlightStatusController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'flight' => ['required', 'string', 'regex:/^[A-Z]{2}\d{1,4}$/i'],
            'date'   => ['sometimes', 'date_format:Y-m-d'],
        ], [
            'flight.required' => 'The flight parameter is required (e.g. AA123).',
            'flight.regex'    => 'Flight must be 2 letters + 1-4 digits (e.g. AA123).',
            'date.date_format' => 'Date must be YYYY-MM-DD format.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $raw  = strtoupper(trim($request->input('flight')));
        $date = $request->input('date', now()->format('Y-m-d'));

        preg_match('/^([A-Z]{2})(\d{1,4})$/', $raw, $m);
        $carrier      = $m[1];
        $flightNumber = $m[2];

        try {
            $client = new AmadeusClient();
            $result = $client->flightStatus($carrier, $flightNumber, $date);
        } catch (\Throwable $e) {
            Log::channel('flight_api')->error('flight_status_error', [
                'flight' => $raw, 'date' => $date, 'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Upstream flight data provider error. Please try again later.',
            ], 502);
        }

        if (!$result || empty($result['data'])) {
            Log::channel('flight_api')->info('flight_not_found', [
                'flight' => $raw, 'date' => $date,
            ]);
            return response()->json(['message' => 'Flight not found'], 404);
        }

        $normalized = $this->normalize($raw, $carrier, $flightNumber, $date, $result['data'][0]);

        Log::channel('flight_api')->info('flight_status_ok', [
            'flight'        => $raw,
            'date'          => $date,
            'status'        => $normalized['status'],
            'delay_minutes' => $normalized['delay_minutes'],
        ]);

        return response()->json($normalized);
    }

    private function normalize(string $flight, string $carrier, string $number, string $date, array $segment): array
    {
        $points = $segment['flightPoints'] ?? [];
        $dep = $points[0] ?? [];
        $arr = $points[1] ?? [];

        $depTimings = $dep['departure'] ?? [];
        $arrTimings = $arr['arrival'] ?? [];

        $legs = $segment['legs'] ?? [];
        $firstLeg = $legs[0] ?? [];

        $providerStatus = $firstLeg['aircraftEquipment']['aircraftType'] ?? null;
        $scheduledDep   = $depTimings['timings'][0]['value'] ?? null;
        $scheduledArr   = $arrTimings['timings'][0]['value'] ?? null;

        $depTimes = $this->extractTimings($depTimings['timings'] ?? []);
        $arrTimes = $this->extractTimings($arrTimings['timings'] ?? []);

        $delayMinutes = 0;
        $status = 'unknown';

        foreach ($segment['segments'] ?? [] as $seg) {
            $partnerStatus = $seg['partnership']['operatingFlight']['statusCode'] ?? null;
            if ($partnerStatus) {
                $providerStatus = $partnerStatus;
            }
        }

        $rawStatus = strtoupper($firstLeg['scheduledLegDuration'] ?? '');
        foreach (($depTimings['timings'] ?? []) as $t) {
            if (($t['qualifier'] ?? '') === 'DL') {
                $delayMinutes = abs((int) preg_replace('/\D/', '', $t['value'] ?? '0'));
            }
        }

        if ($delayMinutes > 0) {
            $status = 'delayed';
        } elseif ($scheduledDep) {
            $status = 'on_time';
        }

        return [
            'provider'            => 'amadeus',
            'flight'              => $flight,
            'carrier'             => $carrier,
            'flight_number'       => $number,
            'date'                => $date,
            'status'              => $status,
            'delay_minutes'       => $delayMinutes,
            'departure'           => [
                'iata'      => $dep['iataCode'] ?? null,
                'scheduled' => $depTimes['scheduled'],
                'estimated' => $depTimes['estimated'],
                'actual'    => $depTimes['actual'],
            ],
            'arrival'             => [
                'iata'      => $arr['iataCode'] ?? null,
                'scheduled' => $arrTimes['scheduled'],
                'estimated' => $arrTimes['estimated'],
                'actual'    => $arrTimes['actual'],
            ],
            'raw_provider_status' => $providerStatus,
        ];
    }

    private function extractTimings(array $timings): array
    {
        $result = ['scheduled' => null, 'estimated' => null, 'actual' => null];
        foreach ($timings as $t) {
            $q = $t['qualifier'] ?? '';
            $v = $t['value'] ?? null;
            match ($q) {
                'ST', 'STD', 'STA' => $result['scheduled'] = $v,
                'TD', 'ETD', 'ETA' => $result['estimated'] = $v,
                'AT'               => $result['actual'] = $v,
                default            => null,
            };
        }
        return $result;
    }
}

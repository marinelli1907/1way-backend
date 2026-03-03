<?php

namespace App\Services\Flights;

class FlightStatusService
{
    public function __construct(
        private AmadeusClient $client
    ) {}

    /**
     * Fetch and normalize flight status. Returns null if not found or on error.
     *
     * @return array{provider: string, flight: string, carrier: string, flight_number: string, date: string, status: string, delay_minutes: int, departure: array, arrival: array, raw_provider_status: string|null}|null
     */
    public function getNormalizedStatus(string $carrier, string $flightNumber, string $date): ?array
    {
        $flight = $carrier . $flightNumber;
        $result = $this->client->flightStatus($carrier, $flightNumber, $date);
        if (!$result || empty($result['data'])) {
            return null;
        }
        return $this->normalize($flight, $carrier, $flightNumber, $date, $result['data'][0]);
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

        foreach (($depTimings['timings'] ?? []) as $t) {
            if (($t['qualifier'] ?? '') === 'DL') {
                $delayMinutes = abs((int) preg_replace('/\D/', '', $t['value'] ?? '0'));
            }
        }

        if ($delayMinutes > 0) {
            $status = 'delayed';
        } elseif (($depTimes['scheduled'] ?? null)) {
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

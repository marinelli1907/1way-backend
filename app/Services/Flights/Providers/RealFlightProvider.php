<?php

namespace App\Services\Flights\Providers;

use App\Services\Flights\DTO\FlightResult;
use App\Services\Flights\FlightProviderInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class RealFlightProvider implements FlightProviderInterface
{
    public function lookupFlightNumber(string $flightNumber, string $date, string $mode): FlightResult
    {
        $apiKey = (string) config('flight.provider_key', '');
        if ($apiKey === '') {
            throw new \RuntimeException('Missing FLIGHT_PROVIDER_KEY');
        }

        $baseUrl = rtrim((string) config('flight.provider_base_url', ''), '/');
        $resp = Http::timeout(12)->get($baseUrl . '/flights', [
            'access_key' => $apiKey,
            'flight_iata' => strtoupper($flightNumber),
            'flight_date' => $date,
        ]);

        if (! $resp->successful()) {
            throw new \RuntimeException('Provider request failed: ' . $resp->status());
        }

        $payload = $resp->json();
        $row = $payload['data'][0] ?? null;
        if (! is_array($row)) {
            throw new \RuntimeException('No matching flight found');
        }

        $schedDep = $this->asUtc($row['departure']['scheduled'] ?? null);
        $schedArr = $this->asUtc($row['arrival']['scheduled'] ?? null);
        $estDep = $this->asUtc($row['departure']['estimated'] ?? null);
        $estArr = $this->asUtc($row['arrival']['estimated'] ?? null);

        return new FlightResult(
            provider: 'real',
            verified: true,
            status: (string) ($row['flight_status'] ?? 'unknown'),
            flightNumber: (string) ($row['flight']['iata'] ?? strtoupper($flightNumber)),
            flightDate: $date,
            airlineCode: (string) ($row['airline']['iata'] ?? null),
            airlineName: (string) ($row['airline']['name'] ?? null),
            depAirportIata: (string) ($row['departure']['iata'] ?? null),
            depAirportName: (string) ($row['departure']['airport'] ?? null),
            arrAirportIata: (string) ($row['arrival']['iata'] ?? null),
            arrAirportName: (string) ($row['arrival']['airport'] ?? null),
            schedDepAt: $schedDep,
            schedArrAt: $schedArr,
            estDepAt: $estDep,
            estArrAt: $estArr,
            terminal: (string) (($row['arrival']['terminal'] ?? $row['departure']['terminal']) ?? null),
            gate: (string) (($row['arrival']['gate'] ?? $row['departure']['gate']) ?? null),
            baggage: (string) ($row['arrival']['baggage'] ?? null),
            raw: is_array($payload) ? $payload : ['payload' => $payload],
        );
    }

    private function asUtc(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse((string) $value)->utc()->toDateTimeString();
    }
}

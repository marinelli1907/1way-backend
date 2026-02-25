<?php

namespace App\Services\Flights\Providers;

use App\Services\Flights\DTO\FlightResult;
use App\Services\Flights\FlightProviderInterface;
use Carbon\Carbon;

class MockFlightProvider implements FlightProviderInterface
{
    public function lookupFlightNumber(string $flightNumber, string $date, string $mode): FlightResult
    {
        $normalized = strtoupper(preg_replace('/[^A-Z0-9]/', '', $flightNumber));
        preg_match('/(\d)$/', $normalized, $m);
        $lastDigit = isset($m[1]) ? (int) $m[1] : 0;

        $status = ($lastDigit % 2 === 0) ? 'scheduled' : 'delayed';
        $depIata = $mode === 'airport_dropoff' ? 'CLE' : 'JFK';
        $arrIata = $mode === 'airport_pickup' ? 'CLE' : 'LAX';
        $depName = $depIata === 'CLE' ? 'Cleveland Hopkins Intl' : 'John F Kennedy Intl';
        $arrName = $arrIata === 'CLE' ? 'Cleveland Hopkins Intl' : 'Los Angeles Intl';

        $base = Carbon::parse($date . ' 10:00:00', 'UTC');
        $schedDep = $base->copy();
        $schedArr = $base->copy()->addHours(2);
        $delay = $status === 'delayed' ? 25 : 0;
        $estDep = $schedDep->copy()->addMinutes($delay);
        $estArr = $schedArr->copy()->addMinutes($delay);

        return new FlightResult(
            provider: 'mock',
            verified: false,
            status: $status,
            flightNumber: $normalized,
            flightDate: $date,
            airlineCode: substr($normalized, 0, 2) ?: 'MK',
            airlineName: 'Mock Airlines',
            depAirportIata: $depIata,
            depAirportName: $depName,
            arrAirportIata: $arrIata,
            arrAirportName: $arrName,
            schedDepAt: $schedDep->toDateTimeString(),
            schedArrAt: $schedArr->toDateTimeString(),
            estDepAt: $estDep->toDateTimeString(),
            estArrAt: $estArr->toDateTimeString(),
            terminal: 'T' . (($lastDigit % 4) + 1),
            gate: chr(65 + ($lastDigit % 6)) . (10 + $lastDigit),
            baggage: $status === 'delayed' ? 'B2' : 'B1',
            raw: [
                'source' => 'mock',
                'mode' => $mode,
            ],
        );
    }
}

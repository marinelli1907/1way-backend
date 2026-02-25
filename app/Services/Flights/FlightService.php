<?php

namespace App\Services\Flights;

use App\Services\Flights\DTO\FlightResult;
use App\Services\Flights\Providers\MockFlightProvider;
use App\Services\Flights\Providers\RealFlightProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FlightService
{
    public function lookupFlightNumber(string $flightNumber, string $date, string $mode): FlightResult
    {
        $configured = strtolower((string) config('flight.provider', 'mock'));

        if ($configured === 'real') {
            try {
                return (new RealFlightProvider())->lookupFlightNumber($flightNumber, $date, $mode);
            } catch (\Throwable $e) {
                Log::warning('Real flight provider failed; fallback to mock', [
                    'error' => $e->getMessage(),
                ]);

                $mock = (new MockFlightProvider())->lookupFlightNumber($flightNumber, $date, $mode);
                $mock->provider = 'mock_fallback';
                $mock->verified = false;
                $mock->raw['fallback_reason'] = $e->getMessage();

                return $mock;
            }
        }

        return (new MockFlightProvider())->lookupFlightNumber($flightNumber, $date, $mode);
    }

    public function recommendedPickupTime(?string $schedArrAt): ?string
    {
        if (! $schedArrAt) {
            return null;
        }

        $buffer = (int) config('flight.default_airport_buffer_min', 25);
        return Carbon::parse($schedArrAt, 'UTC')->addMinutes($buffer)->toDateTimeString();
    }
}

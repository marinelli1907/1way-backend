<?php

namespace App\Services\Flights;

use App\Services\Flights\DTO\FlightResult;

interface FlightProviderInterface
{
    public function lookupFlightNumber(string $flightNumber, string $date, string $mode): FlightResult;
}

<?php

namespace App\Services\Flights\DTO;

class FlightResult
{
    public function __construct(
        public string $provider,
        public bool $verified,
        public ?string $status = null,
        public ?string $flightNumber = null,
        public ?string $flightDate = null,
        public ?string $airlineCode = null,
        public ?string $airlineName = null,
        public ?string $depAirportIata = null,
        public ?string $depAirportName = null,
        public ?string $arrAirportIata = null,
        public ?string $arrAirportName = null,
        public ?string $schedDepAt = null,
        public ?string $schedArrAt = null,
        public ?string $estDepAt = null,
        public ?string $estArrAt = null,
        public ?string $terminal = null,
        public ?string $gate = null,
        public ?string $baggage = null,
        public array $raw = [],
    ) {
    }
}

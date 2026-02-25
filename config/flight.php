<?php

return [
    'provider' => env('FLIGHT_PROVIDER', 'mock'),
    'provider_key' => env('FLIGHT_PROVIDER_KEY'),
    'provider_base_url' => env('FLIGHT_PROVIDER_BASE_URL', 'http://api.aviationstack.com/v1'),
    'default_airport_buffer_min' => (int) env('DEFAULT_AIRPORT_BUFFER_MIN', 25),
];

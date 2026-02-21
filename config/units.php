<?php

/*
|--------------------------------------------------------------------------
| 1Way — Units & Measurement Configuration
|--------------------------------------------------------------------------
|
| All unit display decisions live here. Backend data is stored in SI/metric.
| These settings control how values are DISPLAYED in the Admin UI and API.
|
| Distance stored as: kilometres (km) / metres (m)
| Speed   stored as:  km/h
| Weight  stored as:  kilograms (kg)
| Temp    stored as:  Celsius (°C)
| Currency stored as: base currency amount (float)
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | System locale for the admin panel
    |--------------------------------------------------------------------------
    | 'us'  → miles, mph, lbs, °F, USD
    | 'metric' → km, km/h, kg, °C
    */
    'locale' => env('UNITS_LOCALE', 'us'),

    /*
    |--------------------------------------------------------------------------
    | Distance
    |--------------------------------------------------------------------------
    */
    'distance' => [
        'unit'             => env('DISTANCE_UNIT', 'mi'),   // 'mi' | 'km'
        'label'            => env('DISTANCE_LABEL', 'mi'),
        'km_to_mi'         => 0.621371,
    ],

    /*
    |--------------------------------------------------------------------------
    | Speed
    |--------------------------------------------------------------------------
    */
    'speed' => [
        'unit'             => env('SPEED_UNIT', 'mph'),     // 'mph' | 'km/h'
        'label'            => env('SPEED_LABEL', 'mph'),
        'kmh_to_mph'       => 0.621371,
    ],

    /*
    |--------------------------------------------------------------------------
    | Weight
    |--------------------------------------------------------------------------
    */
    'weight' => [
        'unit'             => env('WEIGHT_UNIT', 'lbs'),    // 'lbs' | 'kg'
        'label'            => env('WEIGHT_LABEL', 'lbs'),
        'kg_to_lbs'        => 2.20462,
    ],

    /*
    |--------------------------------------------------------------------------
    | Temperature
    |--------------------------------------------------------------------------
    */
    'temperature' => [
        'unit'             => env('TEMP_UNIT', 'F'),        // 'F' | 'C'
        'label'            => env('TEMP_LABEL', '°F'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code'             => env('CURRENCY_CODE', 'USD'),
        'symbol'           => env('CURRENCY_SYMBOL', '$'),
        'decimal_places'   => 2,
    ],

];

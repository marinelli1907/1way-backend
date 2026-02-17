<?php

namespace Modules\TripManagement\Service;

use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\TripRequestTime;

class FareCalculator
{
    public static function calculate(TripRequest $trip): array
    {
        $distance = (float)($trip->actual_distance ?? $trip->estimated_distance ?? 0);
        $distance = max(0, round($distance, 2));

        $times = TripRequestTime::where('trip_request_id', $trip->id)->first();
        $minutes = (int)($times->actual_time ?? $times->estimated_time ?? 0);
        $minutes = max(0, $minutes);

        // MVP rates (move to config later)
        $baseFare  = 5.00;
        $perMile   = 2.00;
        $perMinute = 0.50;

        $distanceFee = round($distance * $perMile, 2);
        $timeFee     = round($minutes * $perMinute, 2);
        $subtotal    = round($baseFare + $distanceFee + $timeFee, 2);

        $tips = round((float)($trip->tips ?? 0), 2);

        // MVP admin commission percent
        $adminPct = (float)(config('tripmanagement.admin_commission_pct') ?? 20);

        $adminCommission = round($subtotal * ($adminPct / 100), 2);
        $driverEarnings  = round($subtotal - $adminCommission + $tips, 2);
        $total           = round($subtotal + $tips, 2);

        return [
            'distance'         => $distance,
            'minutes'          => $minutes,
            'base_fare'        => $baseFare,
            'distance_fee'     => $distanceFee,
            'time_fee'         => $timeFee,
            'subtotal'         => $subtotal,
            'tips'             => $tips,
            'admin_pct'        => $adminPct,
            'admin_commission' => $adminCommission,
            'driver_earnings'  => $driverEarnings,
            'total'            => $total,
        ];
    }
}

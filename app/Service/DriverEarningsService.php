<?php

namespace App\Service;

use Modules\TripManagement\Entities\TripRequest;
use Illuminate\Support\Facades\DB;

class DriverEarningsService
{
    public function applyForTrip(TripRequest $trip): void
    {
        if (!$trip->driver_id || !$trip->paid_fare) return;

        $fare = (float)$trip->paid_fare;
        $yearMonth = now()->format('Y-m');

        $config = config('earnings.default');

        $pre = $config['pre_threshold_driver_pct'];
        $post = $config['post_threshold_driver_pct'];
        $threshold = $config['platform_threshold_usd'];

        DB::transaction(function () use ($trip, $fare, $yearMonth, $pre, $post, $threshold) {

            $row = DB::table('driver_monthly_earnings')
                ->where('driver_id', $trip->driver_id)
                ->where('year_month', $yearMonth)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $row = (object)[
                    'platform_earnings' => 0,
                    'driver_earnings' => 0,
                    'total_gross' => 0,
                    'platform_threshold' => $threshold,
                    'threshold_reached_at' => null,
                ];
            }

            $platformSoFar = (float)$row->platform_earnings;

            if ($platformSoFar >= $threshold) {
                $driverShare = $fare * $post;
            } else {
                $platformIfPre = $fare * (1 - $pre);

                if ($platformSoFar + $platformIfPre <= $threshold) {
                    $driverShare = $fare * $pre;
                } else {
                    $fareUntil = ($threshold - $platformSoFar) / (1 - $pre);
                    $fareAfter = $fare - $fareUntil;
                    $driverShare = ($fareUntil * $pre) + ($fareAfter * $post);
                }
            }

            $platformShare = $fare - $driverShare;

            $newPlatform = $platformSoFar + $platformShare;

            DB::table('driver_monthly_earnings')->updateOrInsert(
                [
                    'driver_id' => $trip->driver_id,
                    'year_month' => $yearMonth,
                ],
                [
                    'total_gross' => $row->total_gross + $fare,
                    'driver_earnings' => $row->driver_earnings + $driverShare,
                    'platform_earnings' => $newPlatform,
                    'platform_threshold' => $threshold,
                    'threshold_reached_at' =>
                        $newPlatform >= $threshold && !$row->threshold_reached_at
                            ? now()
                            : $row->threshold_reached_at,
                    'updated_at' => now(),
                    'created_at' => $row->threshold_reached_at ? ($row->created_at ?? now()) : now(),
                ]
            );
        });
    }
}

<?php

namespace Modules\TripManagement\Service;

use Illuminate\Support\Facades\DB;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\TripRequestFee;

class TripSettlementService
{
    public static function settle(string $tripRequestId): array
    {
        return DB::transaction(function () use ($tripRequestId) {

            $trip = TripRequest::lockForUpdate()->findOrFail($tripRequestId);

            // Idempotency guard: if already paid and actual_fare exists, skip
            if (($trip->payment_status ?? null) === 'PAID' && !empty($trip->actual_fare)) {
                return [
                    'status' => 'already_settled',
                    'trip_id' => $trip->id,
                    'actual_fare' => (float)$trip->actual_fare,
                ];
            }

            $fees = TripRequestFee::firstOrCreate(['trip_request_id' => $trip->id]);

            $calc = FareCalculator::calculate($trip);

            // Store commission + tips in fee table (MVP)
            $fees->admin_commission = $calc['admin_commission'];
            $fees->tips             = $calc['tips'];
            $fees->save();

            // Store trip money fields (MVP)
            $trip->actual_fare = $calc['total'];
            $trip->paid_fare   = $calc['total'];
            $trip->due_amount  = 0;

            // MVP: mark paid (gateway later)
            $trip->payment_status = 'PAID';

            $trip->save();

            return [
                'status' => 'settled',
                'trip_id' => $trip->id,
                'total' => $calc['total'],
                'admin_commission' => $calc['admin_commission'],
                'driver_earnings' => $calc['driver_earnings'],
            ];
        });
    }
}

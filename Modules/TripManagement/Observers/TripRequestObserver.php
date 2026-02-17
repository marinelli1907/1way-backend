<?php

namespace Modules\TripManagement\Observers;

use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Entities\TripRequestFee;
use Modules\TripManagement\Entities\TripRequestTime;

class TripRequestObserver
{
    public function created(TripRequest $trip): void
    {
        // trip_request_times.estimated_time is NOT NULL in DB → must set a default
        TripRequestTime::firstOrCreate(
            ['trip_request_id' => $trip->id],
            [
                'estimated_time' => 0,
                'actual_time'    => 0,
                'waiting_time'   => 0,
                'delay_time'     => 0,
                'idle_time'      => 0,
            ]
        );

        TripRequestFee::firstOrCreate(
            ['trip_request_id' => $trip->id],
            [
                'cancellation_fee' => 0,
                'return_fee'       => 0,
                'waiting_fee'      => 0,
                'idle_fee'         => 0,
                'delay_fee'        => 0,
                'vat_tax'          => 0,
                'tips'             => 0,
                'admin_commission' => 0,
            ]
        );
    }
}

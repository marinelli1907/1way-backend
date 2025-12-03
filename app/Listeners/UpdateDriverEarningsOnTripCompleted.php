<?php

namespace App\Listeners;

use App\Service\DriverEarningsService;
use App\Events\DriverTripCompletedEvent;

class UpdateDriverEarningsOnTripCompleted
{
    public function __construct(private DriverEarningsService $service) {}

    public function handle(DriverTripCompletedEvent $event)
    {
        if (!isset($event->tripRequest)) return;
        $this->service->applyForTrip($event->tripRequest);
    }
}

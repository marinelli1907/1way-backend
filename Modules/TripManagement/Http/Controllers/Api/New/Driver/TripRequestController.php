<?php

namespace Modules\TripManagement\Http\Controllers\Api\New\Driver;

use Illuminate\Routing\Controller;
use Modules\Gateways\Traits\Payment;
use Modules\TransactionManagement\Traits\TransactionTrait;
use Modules\TripManagement\Lib\CommonTrait;
use Modules\TripManagement\Lib\CouponCalculationTrait;
use Modules\TripManagement\Service\Interface\TripRequestServiceInterface;
use Modules\TripManagement\Transformers\TripRequestResource;
use Modules\UserManagement\Lib\LevelHistoryManagerTrait;

class TripRequestController extends Controller
{
    use CommonTrait, TransactionTrait, Payment, CouponCalculationTrait, LevelHistoryManagerTrait;

    protected $tripRequestService;

    public function __construct(TripRequestServiceInterface $tripRequestService)
    {
        $this->tripRequestService = $tripRequestService;
    }

    public function currentRideStatus()
    {
        $relations = ['tripStatus', 'customer', 'driver', 'time', 'coordinate', 'time', 'fee', 'parcelRefund'];
        $baseCriteria = ['type' => 'ride_request', 'driver_id' => auth('api')->id()];
        $orderBy = ['created_at' => 'desc'];
        $withAvgRelations = [['customerReceivedReviews', 'rating']];

        // Prefer the only "active" status in this system
        $trip = $this->tripRequestService->findOneBy(
            criteria: array_merge($baseCriteria, ['current_status' => 'picked_up']),
            withAvgRelations: $withAvgRelations,
            relations: $relations,
            orderBy: $orderBy
        );
        if (!$trip) {
            return response()->json(responseFormatter(constant: DEFAULT_200, content: null));
        }

        // Hide only truly irrelevant trips
        if (
            ($trip->fee && $trip->fee->cancelled_by === "driver") ||
            (!$trip->driver_id && $trip->current_status === "cancelled")
        ) {
            return response()->json(responseFormatter(constant: DEFAULT_200, content: null));
        }

        return response()->json(responseFormatter(
            constant: DEFAULT_200,
            content: TripRequestResource::make($trip)
        ));
    }
}

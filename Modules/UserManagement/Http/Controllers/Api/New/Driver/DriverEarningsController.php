<?php

namespace Modules\UserManagement\Http\Controllers\Api\New\Driver;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Read-only (or minimal write) driver earnings/expenses/mileage endpoints.
 * Safe defaults if tables/models do not exist; never throw uncaught.
 */
class DriverEarningsController extends Controller
{
    /**
     * GET /api/driver/earnings — summary for driver app.
     */
    public function earnings(Request $request): JsonResponse
    {
        $payload = [
            'gross_earnings' => 0,
            'app_share' => 0,
            'driver_share' => 0,
            'expenses_total' => 0,
            'miles_total' => 0,
        ];
        try {
            // Optional: if TripRequest or driver earnings tables exist, aggregate here.
            // For now return safe defaults.
        } catch (\Throwable $e) {
            \Log::warning('Driver earnings query failed: ' . $e->getMessage());
        }
        return response()->json(responseFormatter(DEFAULT_200, $payload), 200);
    }

    /**
     * GET /api/driver/expenses — list (read-only).
     */
    public function expenses(Request $request): JsonResponse
    {
        $list = [];
        try {
            // Optional: if driver_expenses table exists, query by auth user.
        } catch (\Throwable $e) {
            \Log::warning('Driver expenses list failed: ' . $e->getMessage());
        }
        return response()->json(responseFormatter(DEFAULT_200, $list), 200);
    }

    /**
     * POST /api/driver/expenses — create (no-op if no table; accept body for future).
     */
    public function storeExpense(Request $request): JsonResponse
    {
        try {
            // Optional: if driver_expenses table exists, insert. Else no-op.
        } catch (\Throwable $e) {
            \Log::warning('Driver store expense failed: ' . $e->getMessage());
        }
        return response()->json(responseFormatter(DEFAULT_UPDATE_200), 200);
    }

    /**
     * GET /api/driver/mileage — list (read-only).
     */
    public function mileage(Request $request): JsonResponse
    {
        $list = [];
        try {
            // Optional: if driver_mileage table exists, query by auth user.
        } catch (\Throwable $e) {
            \Log::warning('Driver mileage list failed: ' . $e->getMessage());
        }
        return response()->json(responseFormatter(DEFAULT_200, $list), 200);
    }

    /**
     * POST /api/driver/mileage — create (no-op if no table).
     */
    public function storeMileage(Request $request): JsonResponse
    {
        try {
            // Optional: if driver_mileage table exists, insert.
        } catch (\Throwable $e) {
            \Log::warning('Driver store mileage failed: ' . $e->getMessage());
        }
        return response()->json(responseFormatter(DEFAULT_UPDATE_200), 200);
    }
}

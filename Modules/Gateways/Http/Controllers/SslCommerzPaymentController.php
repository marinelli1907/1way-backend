<?php

namespace Modules\Gateways\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;

class SslCommerzPaymentController extends Controller
{
    /**
     * Temporary safe constructor
     * Prevents undefined variables & syntax errors
     */
    public function __construct()
    {
        // intentionally empty
    }

    /**
     * Placeholder index route
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'disabled',
            'message' => 'SSLCommerz gateway temporarily disabled'
        ]);
    }

    /**
     * Placeholder success callback
     */
    public function success(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'SSLCommerz success callback (stub)'
        ]);
    }

    /**
     * Placeholder fail callback
     */
    public function fail(Request $request): JsonResponse // Corrected: Renamed from 'cancel' to 'fail'
    {
        return response()->json([
            'status' => 'failed', // Updated: Changed status to 'failed'
            'message' => 'SSLCommerz failed callback (stub)'
        ]);
    }

    /**
     * Placeholder cancel callback
     */
    public function cancel(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'cancelled',
            'message' => 'SSLCommerz cancelled callback (stub)'
        ]);
    }
}

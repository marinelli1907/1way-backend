<?php

namespace Modules\ZoneManagement\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\ZoneManagement\Service\GeoZoneService;

class ServiceZoneApiController extends Controller
{
    protected GeoZoneService $geo;

    public function __construct(GeoZoneService $geo)
    {
        $this->geo = $geo;
    }

    /**
     * GET /api/zones/contains?lat=...&lng=...
     */
    public function contains(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $zone = $this->geo->findZoneForPoint((float) $request->lat, (float) $request->lng);

        return response()->json([
            'inside'    => (bool) $zone,
            'zone_id'   => $zone?->id,
            'zone_name' => $zone?->name,
        ]);
    }
}

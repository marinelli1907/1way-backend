<?php

namespace Modules\ZoneManagement\Http\Controllers\Web\New\Admin;

use App\Http\Controllers\BaseController;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\ZoneManagement\Entities\Zone;
use Modules\ZoneManagement\Service\Interface\ZoneServiceInterface;

/**
 * Part B — GeoJSON import / export for zones.
 *
 * Admin can paste a GeoJSON FeatureCollection or Feature and we convert
 * each Polygon feature into a Zone record.
 */
class ZoneGeoJsonController extends BaseController
{
    use AuthorizesRequests;

    public function __construct(protected ZoneServiceInterface $zoneService)
    {
        parent::__construct($zoneService);
    }

    /** GET /admin/zone/geojson-import — show the import form */
    public function importForm(): \Illuminate\View\View
    {
        $this->authorize('zone_add');
        return view('zonemanagement::admin.zone.geojson-import');
    }

    /**
     * POST /admin/zone/geojson-import
     * Accept raw GeoJSON text or an uploaded .geojson / .json file.
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('zone_add');

        $request->validate([
            'geojson_text' => 'nullable|string',
            'geojson_file' => 'nullable|file|mimes:json,geojson|max:2048',
            'name_property' => 'nullable|string|max:100',  // which GeoJSON property to use as zone name
        ]);

        // Get raw JSON string
        if ($request->hasFile('geojson_file')) {
            $raw = file_get_contents($request->file('geojson_file')->getRealPath());
        } elseif ($request->filled('geojson_text')) {
            $raw = $request->input('geojson_text');
        } else {
            Toastr::error(translate('Please provide a GeoJSON file or text.'));
            return back();
        }

        $geoJson = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Toastr::error(translate('Invalid JSON. Please check your GeoJSON.'));
            return back()->withInput();
        }

        // Normalise to a features array
        $features = match ($geoJson['type'] ?? '') {
            'FeatureCollection' => $geoJson['features'] ?? [],
            'Feature'           => [$geoJson],
            'Polygon'           => [['type' => 'Feature', 'geometry' => $geoJson, 'properties' => []]],
            default             => [],
        };

        if (empty($features)) {
            Toastr::error(translate('No polygon features found in the GeoJSON.'));
            return back()->withInput();
        }

        $nameProperty = $request->input('name_property', 'name');
        $imported     = 0;
        $skipped      = 0;

        DB::beginTransaction();
        try {
            foreach ($features as $feature) {
                $geometry = $feature['geometry'] ?? $feature;
                if (($geometry['type'] ?? '') !== 'Polygon') {
                    $skipped++;
                    continue;
                }

                $zoneName = $feature['properties'][$nameProperty]
                    ?? $feature['properties']['NAME']
                    ?? $feature['properties']['name']
                    ?? ('Imported Zone ' . ($imported + $skipped + 1));

                // Build spatial Polygon from GeoJSON ring
                $rings     = $geometry['coordinates'];
                $outerRing = $rings[0]; // GeoJSON: [lng, lat] pairs

                $points = collect($outerRing)->map(fn($c) => new Point($c[1], $c[0]));
                $polygon = new Polygon([new LineString($points->all())]);

                $nextReadableId = (Zone::max('readable_id') ?? 0) + 1;

                Zone::create([
                    'name'               => $zoneName,
                    'readable_id'        => $nextReadableId,
                    'coordinates'        => $polygon,
                    'is_active'          => true,
                    'extra_fare_status'  => false,
                    'extra_fare_fee'     => 0,
                    'pricing_multiplier' => 1.0,
                ]);

                $imported++;
            }

            DB::commit();
            Toastr::success(translate("Imported $imported zone(s). Skipped $skipped non-polygon feature(s)."));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('GeoJSON import error: ' . $e->getMessage());
            Toastr::error(translate('Import failed: ') . $e->getMessage());
            return back()->withInput();
        }

        return redirect()->route('admin.zone.index');
    }

    /**
     * GET /admin/zone/{id}/export-geojson
     * Returns a single zone as a GeoJSON Feature download.
     */
    public function export(string $id): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('zone_view');
        $zone = Zone::findOrFail($id);

        // Convert spatial polygon → GeoJSON
        $geoJson = [
            'type'     => 'Feature',
            'properties' => [
                'id'                 => $zone->id,
                'name'               => $zone->name,
                'pricing_multiplier' => $zone->pricing_multiplier,
                'is_active'          => $zone->is_active,
            ],
            'geometry' => json_decode($zone->coordinates->toJson(), true),
        ];

        $filename = 'zone-' . $zone->readable_id . '-' . str_replace(' ', '-', strtolower($zone->name)) . '.geojson';

        return response()->json($geoJson)
            ->withHeaders([
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Type'        => 'application/geo+json',
            ]);
    }

    /**
     * GET /admin/zone/export-all-geojson
     * Returns ALL active zones as a GeoJSON FeatureCollection.
     */
    public function exportAll(): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('zone_view');
        $zones = Zone::ofStatus(1)->get();

        $features = $zones->map(fn($z) => [
            'type'       => 'Feature',
            'properties' => [
                'id'                 => $z->id,
                'name'               => $z->name,
                'readable_id'        => $z->readable_id,
                'pricing_multiplier' => $z->pricing_multiplier,
            ],
            'geometry'   => json_decode($z->coordinates->toJson(), true),
        ]);

        $collection = ['type' => 'FeatureCollection', 'features' => $features];

        return response()->json($collection)
            ->withHeaders([
                'Content-Disposition' => 'attachment; filename="1way-zones.geojson"',
                'Content-Type'        => 'application/geo+json',
            ]);
    }
}

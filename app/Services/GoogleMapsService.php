<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $key;

    public function __construct()
    {
        $this->key = config('services.google.maps_key') ?? '';
    }

    // ─── Distance Matrix ─────────────────────────────────────────────────

    /**
     * @param string $origin   place_id:<id> or "lat,lng"
     * @param string $destination  same format
     */
    public function distanceMatrix(string $origin, string $destination, string $mode = 'driving'): array
    {
        $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins'       => $origin,
            'destinations'  => $destination,
            'mode'          => $mode,
            'departure_time' => 'now',
            'key'           => $this->key,
        ]);

        $body = $response->json();

        if (!$response->successful()) {
            Log::error('[GoogleMapsService] Distance Matrix HTTP error', [
                'status' => $response->status(),
            ]);
            throw new GoogleMapsException('Google Distance Matrix HTTP ' . $response->status());
        }

        $topStatus = $body['status'] ?? 'UNKNOWN';
        if ($topStatus !== 'OK') {
            Log::error('[GoogleMapsService] Distance Matrix API error', [
                'status' => $topStatus,
                'error'  => $body['error_message'] ?? null,
            ]);
            throw new GoogleMapsException(
                $body['error_message'] ?? "Distance Matrix failed: {$topStatus}",
                $topStatus
            );
        }

        $element = $body['rows'][0]['elements'][0] ?? [];
        $elStatus = $element['status'] ?? 'NOT_FOUND';

        if ($elStatus !== 'OK') {
            Log::warning('[GoogleMapsService] Distance Matrix element status', [
                'element_status' => $elStatus,
            ]);
            return [
                'distance_meters'            => 0,
                'duration_seconds'           => 0,
                'duration_in_traffic_seconds' => 0,
                'raw_status'                 => $elStatus,
                'raw_error_message'          => "No route found ({$elStatus})",
            ];
        }

        return [
            'distance_meters'            => $element['distance']['value'] ?? 0,
            'duration_seconds'           => $element['duration']['value'] ?? 0,
            'duration_in_traffic_seconds' => $element['duration_in_traffic']['value']
                                             ?? $element['duration']['value']
                                             ?? 0,
            'raw_status'                 => $elStatus,
            'raw_error_message'          => null,
        ];
    }

    // ─── Address Validation ──────────────────────────────────────────────

    public function validateAddress(string $address): array
    {
        $response = Http::timeout(10)
            ->withHeaders(['X-Goog-Api-Key' => $this->key])
            ->post('https://addressvalidation.googleapis.com/v1:validateAddress', [
                'address' => ['addressLines' => [$address]],
            ]);

        $body = $response->json();

        if (!$response->successful()) {
            Log::error('[GoogleMapsService] Address Validation HTTP error', [
                'status' => $response->status(),
            ]);
            throw new GoogleMapsException('Address Validation HTTP ' . $response->status());
        }

        $result  = $body['result'] ?? [];
        $verdict = $result['verdict'] ?? [];
        $addr    = $result['address'] ?? [];

        $isComplete = ($verdict['addressComplete'] ?? false)
            && ($verdict['hasUnconfirmedComponents'] ?? true) === false;

        return [
            'formatted_address' => $addr['formattedAddress'] ?? $address,
            'verdict'           => $isComplete ? 'complete' : 'needs_correction',
            'components'        => collect($addr['addressComponents'] ?? [])
                ->mapWithKeys(fn($c) => [$c['componentType'] => $c['componentName']['text'] ?? ''])
                ->all(),
        ];
    }

    // ─── Snap to Roads ───────────────────────────────────────────────────

    /**
     * @param array $pathLatLngList  array of [lat, lng] pairs
     */
    public function snapToRoads(array $pathLatLngList): array
    {
        $path = collect($pathLatLngList)
            ->map(fn($p) => $p[0] . ',' . $p[1])
            ->implode('|');

        $response = Http::timeout(10)->get('https://roads.googleapis.com/v1/snapToRoads', [
            'path'         => $path,
            'interpolate'  => true,
            'key'          => $this->key,
        ]);

        $body = $response->json();

        if (!$response->successful()) {
            Log::error('[GoogleMapsService] Snap to Roads HTTP error', [
                'status' => $response->status(),
            ]);
            throw new GoogleMapsException('Snap to Roads HTTP ' . $response->status());
        }

        if (isset($body['error'])) {
            throw new GoogleMapsException(
                $body['error']['message'] ?? 'Snap to Roads failed',
                $body['error']['status'] ?? 'UNKNOWN'
            );
        }

        return collect($body['snappedPoints'] ?? [])
            ->map(fn($p) => [
                'lat'      => $p['location']['latitude'] ?? 0,
                'lng'      => $p['location']['longitude'] ?? 0,
                'place_id' => $p['placeId'] ?? null,
                'original_index' => $p['originalIndex'] ?? null,
            ])
            ->all();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Build an origin/destination string from either a place_id or lat/lng.
     */
    public static function formatOrigin(?string $placeId, ?float $lat, ?float $lng): string
    {
        if ($placeId) {
            return "place_id:{$placeId}";
        }
        return "{$lat},{$lng}";
    }
}

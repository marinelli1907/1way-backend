<?php

namespace App\Services\Flights;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmadeusClient
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $env = config('services.amadeus.env', 'test');
        $this->baseUrl = $env === 'production'
            ? 'https://api.amadeus.com'
            : 'https://test.api.amadeus.com';

        $this->clientId     = config('services.amadeus.client_id');
        $this->clientSecret = config('services.amadeus.client_secret');
    }

    public function getAccessToken(): string
    {
        $cached = Cache::get('amadeus_oauth_token');
        if ($cached) {
            return $cached;
        }
        return $this->fetchNewToken();
    }

    private function fetchNewToken(): string
    {
        $response = Http::asForm()->post("{$this->baseUrl}/v1/security/oauth2/token", [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Amadeus OAuth failed: HTTP ' . $response->status());
        }

        $data      = $response->json();
        $token     = $data['access_token'] ?? null;
        $expiresIn = (int) ($data['expires_in'] ?? 1799);

        if (!$token) {
            throw new \RuntimeException('Amadeus OAuth returned no access_token');
        }

        Cache::put('amadeus_oauth_token', $token, max($expiresIn - 60, 60));

        return $token;
    }

    /**
     * Look up on-demand flight status.
     *
     * @return array{data: array, meta?: array}|null  Decoded JSON body or null on 404-like empty result
     * @throws \RuntimeException on auth/server errors
     */
    public function flightStatus(string $carrierCode, string $flightNumber, string $date): ?array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->timeout(15)
            ->get("{$this->baseUrl}/v2/schedule/flights", [
                'carrierCode'            => $carrierCode,
                'flightNumber'           => $flightNumber,
                'scheduledDepartureDate' => $date,
            ]);

        if ($response->status() === 401) {
            Cache::forget('amadeus_oauth_token');
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(15)
                ->get("{$this->baseUrl}/v2/schedule/flights", [
                    'carrierCode'            => $carrierCode,
                    'flightNumber'           => $flightNumber,
                    'scheduledDepartureDate' => $date,
                ]);
        }

        if ($response->status() === 404 || ($response->successful() && empty($response->json('data')))) {
            return null;
        }

        if (!$response->successful()) {
            Log::channel('flight_api')->error('Amadeus API error', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
            throw new \RuntimeException('Amadeus API error: HTTP ' . $response->status());
        }

        return $response->json();
    }
}

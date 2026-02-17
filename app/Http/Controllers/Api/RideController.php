<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RideController extends Controller
{
    public function index(Request $request)
    {
        $query = Ride::query()->with('event')->orderBy('scheduled_at');

        // Optional filters
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->query('event_id'));
        }

        if ($request->filled('rider_id')) {
            $query->where('rider_id', $request->query('rider_id'));
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->query('driver_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function show(Ride $ride)
    {
        return response()->json([
            'data' => $ride->load('event'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'integer'],
            'rider_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'pickup_address' => ['nullable', 'string', 'max:255'],
            'dropoff_address' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'price_estimate_cents' => ['nullable', 'integer', 'min:0'],
            'final_price_cents' => ['nullable', 'integer', 'min:0'],
        ]);

        if (!isset($validated['status']) || trim((string)$validated['status']) === '') {
            $validated['status'] = 'requested';
        }

        $ride = Ride::create($validated);

        return response()->json([
            'data' => $ride->load('event'),
        ], 201);
    }

    public function update(Request $request, Ride $ride)
    {
        $validated = $request->validate([
            'event_id' => ['nullable', 'integer'],
            'rider_id' => ['nullable', 'integer'],
            'driver_id' => ['nullable', 'integer'],
            'pickup_address' => ['nullable', 'string', 'max:255'],
            'dropoff_address' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:255'],
            'price_estimate_cents' => ['nullable', 'integer', 'min:0'],
            'final_price_cents' => ['nullable', 'integer', 'min:0'],
        ]);

        $ride->update($validated);

        return response()->json([
            'data' => $ride->fresh()->load('event'),
        ]);
    }

    public function destroy(Ride $ride)
    {
        $ride->delete();

        return response()->json([
            'ok' => true,
        ]);
    }
}

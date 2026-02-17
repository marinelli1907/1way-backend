<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        // Public for now. Optional filtering: ?user_id=123
        $q = Event::query()->orderBy('starts_at', 'asc');

        if ($request->filled('user_id')) {
            $q->where('user_id', $request->integer('user_id'));
        }

        return response()->json([
            'data' => $q->get(),
        ]);
    }

    public function show(Event $event)
    {
        return response()->json(['data' => $event]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'        => 'nullable|integer',
            'title'          => 'required|string|max:255',
            'starts_at'      => 'required|date',
            'ends_at'        => 'nullable|date|after_or_equal:starts_at',
            'timezone'       => 'nullable|string|max:64',
            'is_public'      => 'nullable|boolean',
            'notes'          => 'nullable|string',
            'location_name'  => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:255',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
        ]);

        // If/when auth is enabled, attach the authenticated user.
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        // Default is_public true if not provided
        if (!array_key_exists('is_public', $validated)) {
            $validated['is_public'] = true;
        }

        $event = Event::create($validated);

        return response()->json(['data' => $event], 201);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'user_id'        => 'nullable|integer',
            'title'          => 'sometimes|required|string|max:255',
            'starts_at'      => 'sometimes|required|date',
            'ends_at'        => 'nullable|date|after_or_equal:starts_at',
            'timezone'       => 'nullable|string|max:64',
            'is_public'      => 'nullable|boolean',
            'notes'          => 'nullable|string',
            'location_name'  => 'nullable|string|max:255',
            'address'        => 'nullable|string|max:255',
            'lat'            => 'nullable|numeric',
            'lng'            => 'nullable|numeric',
        ]);

        // If/when auth is enabled, force user_id to the authenticated user.
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $event->update($validated);

        return response()->json(['data' => $event]);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
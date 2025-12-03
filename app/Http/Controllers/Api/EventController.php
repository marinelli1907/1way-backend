<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        return Event::where('user_id', $request->user()->id)
            ->orderBy('start_time')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'location_name' => 'nullable|string|max:255',
            'address'       => 'nullable|string|max:255',
            'lat'           => 'nullable|numeric',
            'lng'           => 'nullable|numeric',
            'start_time'    => 'required|date',
            'end_time'      => 'nullable|date|after_or_equal:start_time',
            'source'        => 'nullable|string|max:50',
            'meta'          => 'nullable|array',
        ]);

        $data['user_id'] = $request->user()->id;

        return Event::create($data);
    }

    public function show(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);
        return $event;
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);

        $data = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'description'   => 'sometimes|nullable|string',
            'location_name' => 'sometimes|nullable|string|max:255',
            'address'       => 'sometimes|nullable|string|max:255',
            'lat'           => 'sometimes|nullable|numeric',
            'lng'           => 'sometimes|nullable|numeric',
            'start_time'    => 'sometimes|date',
            'end_time'      => 'sometimes|nullable|date|after_or_equal:start_time',
            'source'        => 'sometimes|string|max:50',
            'meta'          => 'sometimes|array',
        ]);

        $event->update($data);
        return $event;
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorizeEvent($request, $event);
        $event->delete();
        return ['success' => true];
    }

    protected function authorizeEvent(Request $request, Event $event): void
    {
        if ($event->user_id !== $request->user()->id) {
            abort(403, 'Not allowed.');
        }
    }
}

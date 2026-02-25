<?php

namespace Modules\TripManagement\Http\Controllers\Api\Customer;

use App\Services\Flights\FlightService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TripManagement\Entities\TripRequest;
use Modules\TripManagement\Http\Requests\FlightLookupRequest;

class FlightController extends Controller
{
    public function __construct(private readonly FlightService $flightService)
    {
    }

    public function lookup(FlightLookupRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (($data['input_type'] ?? null) === 'reservation') {
            return response()->json(responseFormatter(DEFAULT_200, [
                'response_code' => 'flight_reservation_not_supported',
                'provider' => 'mock',
                'verified' => false,
                'flight' => null,
            ]));
        }

        $flight = $this->flightService->lookupFlightNumber(
            (string) $data['flight_number'],
            (string) $data['date'],
            (string) $data['ride_airport_mode']
        );

        $recommended = $this->flightService->recommendedPickupTime($flight->schedArrAt);

        return response()->json(responseFormatter(DEFAULT_200, [
            'response_code' => 'flight_lookup_200',
            'provider' => $flight->provider,
            'verified' => $flight->verified,
            'recommended_pickup_time' => $recommended,
            'flight' => [
                'input_type' => 'flight_number',
                'flight_number' => $flight->flightNumber,
                'flight_date' => $flight->flightDate,
                'airline_code' => $flight->airlineCode,
                'airline_name' => $flight->airlineName,
                'status' => $flight->status,
                'dep_airport_iata' => $flight->depAirportIata,
                'dep_airport_name' => $flight->depAirportName,
                'arr_airport_iata' => $flight->arrAirportIata,
                'arr_airport_name' => $flight->arrAirportName,
                'sched_dep_at' => $flight->schedDepAt,
                'sched_arr_at' => $flight->schedArrAt,
                'est_dep_at' => $flight->estDepAt,
                'est_arr_at' => $flight->estArrAt,
                'terminal' => $flight->terminal,
                'gate' => $flight->gate,
                'baggage' => $flight->baggage,
            ],
        ]));
    }

    public function ics(Request $request, string $trip_request_id)
    {
        $trip = TripRequest::query()->with(['flightDetail'])->find($trip_request_id);

        if (! $trip || $trip->customer_id !== auth('api')->id()) {
            return response()->json(responseFormatter(DEFAULT_404), 404);
        }

        if (! $trip->flightDetail) {
            return response()->json(responseFormatter(DEFAULT_404), 404);
        }

        $flight = $trip->flightDetail;
        $start = $this->flightService->recommendedPickupTime($flight->sched_arr_at?->utc()->toDateTimeString())
            ?? optional($flight->sched_arr_at)->utc()->toDateTimeString()
            ?? Carbon::now('UTC')->toDateTimeString();
        $end = Carbon::parse($start, 'UTC')->addMinutes(45)->toDateTimeString();
        $flightNumber = $flight->flight_number ?: 'N/A';

        $ics = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//1WayRide//Flight MVP//EN\r\n"
            . "BEGIN:VEVENT\r\n"
            . "UID:" . $trip->id . "-flight@1way\r\n"
            . "DTSTAMP:" . Carbon::now('UTC')->format('Ymd\THis\Z') . "\r\n"
            . "DTSTART:" . Carbon::parse($start, 'UTC')->format('Ymd\THis\Z') . "\r\n"
            . "DTEND:" . Carbon::parse($end, 'UTC')->format('Ymd\THis\Z') . "\r\n"
            . "SUMMARY:Airport Run - " . $this->escapeIcs($flightNumber) . "\r\n"
            . "DESCRIPTION:" . $this->escapeIcs((string) ($flight->status ?? 'scheduled')) . "\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="trip-' . $trip->ref_id . '-flight.ics"',
        ]);
    }

    private function escapeIcs(string $text): string
    {
        return str_replace(["\\", ";", ",", "\n", "\r"], ["\\\\", "\\;", "\\,", "\\n", ''], $text);
    }
}

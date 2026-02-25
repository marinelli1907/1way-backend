<?php

namespace Modules\TripManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlightLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'input_type' => 'required|in:flight_number,reservation',
            'flight_number' => 'required_if:input_type,flight_number|nullable|string|max:30',
            'date' => 'required|date_format:Y-m-d',
            'ride_airport_mode' => 'required|in:airport_pickup,airport_dropoff',
            'reservation_code' => 'nullable|string|max:30',
            'last_name' => 'nullable|string|max:120',
        ];
    }
}

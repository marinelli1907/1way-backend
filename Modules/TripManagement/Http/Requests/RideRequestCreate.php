<?php

namespace Modules\TripManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RideRequestCreate extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pickup_coordinates' => 'required',
            'destination_coordinates' => 'required',
            'customer_coordinates' => 'required',
            'estimated_time' => 'required',
            'estimated_distance' => 'required',
            'estimated_fare' => 'required',
            'actual_fare' => 'sometimes',
            'vehicle_category_id' => 'required_if:type,==,ride_request',
            'note' => 'sometimes',
            'pickup_address' => 'required',
            'destination_address' => 'required',
            'customer_request_coordinates' => 'required',
            'type' => 'required|in:parcel,ride_request',
            'sender_name' => 'required_if:type,==,parcel',
            'sender_phone' => 'required_if:type,==,parcel',
            'sender_address' => 'required_if:type,==,parcel',
            'receiver_name' => 'required_if:type,==,parcel',
            'receiver_phone' => 'required_if:type,==,parcel',
            'receiver_address' => 'required_if:type,==,parcel',
            'parcel_category_id' => 'required_if:type,==,parcel',
            'weight' => 'required_if:type,==,parcel',
            'payer' => 'required_if:type,==,parcel',
            'ride_airport_mode' => 'sometimes|nullable|in:airport_pickup,airport_dropoff',
            'flight_input_type' => 'required_with:ride_airport_mode|nullable|in:flight_number,reservation',
            'flight_number' => 'required_if:flight_input_type,flight_number|nullable|string|max:30',
            'flight_date' => 'required_if:flight_input_type,flight_number|nullable|date_format:Y-m-d',
            'reservation_code' => 'required_if:flight_input_type,reservation|nullable|string|max:30',
            'last_name' => 'required_if:flight_input_type,reservation|nullable|string|max:120'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}

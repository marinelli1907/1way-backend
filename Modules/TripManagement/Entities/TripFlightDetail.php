<?php

namespace Modules\TripManagement\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class TripFlightDetail extends Model
{
    use HasUuid;

    protected $fillable = [
        'trip_request_id',
        'provider',
        'verified',
        'input_type',
        'flight_number',
        'flight_date',
        'airline_code',
        'airline_name',
        'status',
        'dep_airport_iata',
        'dep_airport_name',
        'arr_airport_iata',
        'arr_airport_name',
        'sched_dep_at',
        'sched_arr_at',
        'est_dep_at',
        'est_arr_at',
        'terminal',
        'gate',
        'baggage',
        'raw',
        'last_synced_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'flight_date' => 'date',
        'sched_dep_at' => 'datetime',
        'sched_arr_at' => 'datetime',
        'est_dep_at' => 'datetime',
        'est_arr_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'raw' => 'array',
    ];

    public function tripRequest()
    {
        return $this->belongsTo(TripRequest::class, 'trip_request_id');
    }
}

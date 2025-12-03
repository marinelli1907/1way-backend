<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Job extends Model
{
    use HasFactory;

    protected $table = 'jobs';

    protected $fillable = [
        'driver_id',
        'passenger_id',
        'pickup_address',
        'pickup_lat',
        'pickup_lng',
        'dropoff_address',
        'dropoff_lat',
        'dropoff_lng',
        'pickup_time',
        'status',
        'gross_fare',
        'app_share',
        'driver_share',
        'distance_km',
        'duration_min',
        'passenger_name',
        'passenger_phone',
        'notes',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'pickup_lat'  => 'float',
        'pickup_lng'  => 'float',
        'dropoff_lat' => 'float',
        'dropoff_lng' => 'float',
        'gross_fare'  => 'float',
        'app_share'   => 'float',
        'driver_share'=> 'float',
        'distance_km' => 'float',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }
}

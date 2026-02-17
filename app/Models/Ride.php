<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ride extends Model
{
    protected $fillable = [
        'event_id',
        'rider_id',
        'driver_id',
        'pickup_address',
        'dropoff_address',
        'scheduled_at',
        'status',
        'price_estimate_cents',
        'final_price_cents',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'price_estimate_cents' => 'integer',
        'final_price_cents' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
